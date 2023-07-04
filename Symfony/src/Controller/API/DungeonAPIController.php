<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Entity\Character;
use App\Entity\DungeonInstance;
use App\Repository\AttackRepository;
use App\Repository\DungeonRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\APIService;
use App\Service\Dungeon\DungeonGenerationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DungeonAPIController extends AbstractAPIController
{
    private APIService $apiService;
    private UserRepository $userRepository;
    private DungeonRepository $dungeonRepository;
    private DungeonGenerationService $dungeonGenerationService;
    private EntityManagerInterface $em;
    private SpeciesRepository $speciesRepository;
    private TypeRepository $typeRepository;
    private AttackRepository $attackRepository;

    private User $user;
    private Character $character;

    public function __construct(APIService $apiService, UserRepository $userRepository, DungeonRepository $dungeonRepository,
                                DungeonGenerationService $dungeonGenerationService, EntityManagerInterface $em,
                                SpeciesRepository $speciesRepository, TypeRepository $typeRepository, AttackRepository $attackRepository)
    {
        $this->apiService = $apiService;
        $this->userRepository = $userRepository;
        $this->dungeonRepository = $dungeonRepository;
        $this->dungeonGenerationService = $dungeonGenerationService;
        $this->em = $em;
        $this->speciesRepository = $speciesRepository;
        $this->typeRepository = $typeRepository;
        $this->attackRepository = $attackRepository;
    }

    #[Route('/api/dungeon/check/enter-validity', name: 'api_dungeon_check_enter-validity')]
    public function isAbleToEnterDungeon(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        if($this->character->getCurrentExplorationDungeonInstance() !== null){
            return new JsonResponse(['result' => false], 200);
        }

        return new JsonResponse(['result' => $this->character->getTimers()->canEnterDungeon()], 200);
    }

    #[Route('/api/dungeon/instance/join', name: 'api_dungeon_instance_join')]
    public function joinDungeonInstance(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId", "leaderDiscordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $leaderUser = $this->userRepository->findOneBy(['discordTag' => $post->data->leaderDiscordUserId]);

        if($leaderUser === null || $leaderUser->getCharacter() === null){
            return new JsonResponse(['message' => 'ID de leader incorrect.'], 400);
        }

        $dungeonInstance = $leaderUser->getCharacter()->getCurrentExplorationDungeonInstance();

        if($this->character->getTimers()->canEnterDungeon() && $this->character->getCurrentExplorationDungeonInstance() === null){
            $dungeonInstance->addExplorer($this->character);
            $this->em->flush();

            return new JsonResponse(['message' => 'Le personnage à bien rejoint le groupe.'], 200);
        }
        else {
            return new JsonResponse(['message' => 'Le personnage ne peut pas entrer dans le groupe.'], 400);
        }
    }

    #[Route('/api/dungeon/instance/show', name: 'api_dungeon_instance_show')]
    public function showDungeonInstance(Request $request)
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }
        
        if($this->character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $this->character->getCurrentExplorationDungeonInstance();

        $response = $this->render('api_dungeon.html.twig',[
            'dungeon' => $dungeonInstance->getContent()['dungeon'],
            'data' => $dungeonInstance->getContent()['data'],
            'currentExplorersPosition' => $dungeonInstance->getCurrentExplorersPosition()
        ]);

        return new JsonResponse([
            'webLink' => $this->generateUrl('app_dungeon', ['id' => $dungeonInstance->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'htmlContent' => $response->getContent(),
            'instanceStatus' => $dungeonInstance->getStatus(),
        ], 200);
    }

    #[Route('/api/dungeon/instance/members', name: 'api_dungeon_instance_members')]
    public function getDungeonInstanceMembers(Request $request)
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }
        
        if($this->character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $this->character->getCurrentExplorationDungeonInstance();
        $explorersList = [];

        foreach ($dungeonInstance->getExplorers() as $explorer) {
            $typeList = [];

            foreach ($explorer->getTypes() as $type) {
                $typeList[] = $type->getName();
            }

            $isLeader = false;

            if($explorer === $dungeonInstance->getLeader()){
                $isLeader = true;
            }

            $explorersList[] = [
                'userId' => $explorer->getUserI()->getDiscordTag(),
                'name' => $explorer->getName(),
                'gender' => $explorer->getGender(),
                'species' => $explorer->getSpecies()->getName(),
                'type' => $typeList,
                'level' => $explorer->getLevel(),
                'isLeader' => $isLeader
            ];
        }

        return new JsonResponse([
            'explorerList' => $explorersList
        ]);
    }

    #[Route('/api/dungeon/instance/create', name: 'api_dungeon_instance_create')]
    public function createDungeonInstance(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId", "dungeonName"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $dungeon = $this->dungeonRepository->findOneBy(['name' => $post->data->dungeonName]);

        if($dungeon === null){
            return new JsonResponse(['message' => 'Dungeon does not exist'], 400);
        }

        if($this->character->getCurrentExplorationDungeonInstance() !== null){
            return new JsonResponse(['message' => 'Vous êtes déjà dans un donjon.'], 400);
        }

        if(!$this->character->getTimers()->canEnterDungeon()){
            return new JsonResponse(['message' => 'Vous avez besoin de vous reposer, attendez avant de retourner explorer les donjons.'], 400);
        }

        $generatedDungeon = $this->dungeonGenerationService->generateDungeon($dungeon, $dungeon->getSize());
        $dungeonInstance = new DungeonInstance();
        $dungeonInstance->setContent($generatedDungeon['content'])
                        ->setDateCreated(new DateTime())
                        ->setDungeon($dungeon)
                        ->setCurrentExplorersPosition($generatedDungeon['currentExplorersPosition'])
                        ->setLeader($this->character)
                        ->addExplorer($this->character)
                        ->setStatus(DungeonInstance::DUNGEON_STATUS_PREPARATION);

        $this->em->persist($dungeonInstance);
        $this->em->flush();

        return new JsonResponse(['message' => 'Dungeon Instance Created'], 200);
    }

    #[Route('/api/dungeon/instance/move', name: 'api_dungeon_instance_move')]
    public function explorersMoveDungeonInstance(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId", "direction"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $allowedDirections = [DungeonInstance::MOVE_DIRECTION_DOWN, DungeonInstance::MOVE_DIRECTION_UP, DungeonInstance::MOVE_DIRECTION_LEFT, DungeonInstance::MOVE_DIRECTION_RIGHT];

        if(!in_array($post->data->direction, $allowedDirections)){
            return new JsonResponse([
                'message' => 'Not valid direction.'
            ], 400);
        }

        if($this->character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $this->character->getCurrentExplorationDungeonInstance();

        if(!$this->hasCharacterInDungeon($this->user, $dungeonInstance) || $this->user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Character is not the leader.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return new JsonResponse([
                'message' => "DungeonInstance is not in exploration phase."
            ], 400);
        }

        $hasMoved = $dungeonInstance->moveExplorers($post->data->direction, $this->em);

        if($hasMoved === false){
            return new JsonResponse([
                'message' => 'Impossible movement.'
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Movement done.'
        ], 200);
    }

    #[Route('/api/dungeon/instance/fight', name: 'api_dungeon_instance_fight')]
    public function fightMonstersDungeonInstance(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        if($this->character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $this->character->getCurrentExplorationDungeonInstance();

        if(!$this->hasCharacterInDungeon($this->user, $dungeonInstance) || $this->user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Character is not the leader.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return new JsonResponse([
                'message' => "DungeonInstance is not in exploration phase."
            ], 400);
        }

        $data = $dungeonInstance->fightCurrentPositionMonsters($this->speciesRepository, $this->typeRepository, $this->attackRepository, $this->em);

        return new JsonResponse([
            'combatLogUrl' => $this->generateUrl('app_combat', ['id' => $data['combatLogId']], UrlGeneratorInterface::ABSOLUTE_URL),
            'message' => 'Fight done.',
            'victory' => $data['victory']
        ], 200);
    }

    #[Route('/api/dungeon/instance/interact', name: 'api_dungeon_instance_interact')]
    public function interactWithDungeonInstance(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        if($this->character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $this->character->getCurrentExplorationDungeonInstance();

        if(!$this->hasCharacterInDungeon($this->user, $dungeonInstance) || $this->user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Character is not the leader.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return new JsonResponse([
                'message' => "DungeonInstance is not in exploration phase."
            ], 400);
        }

        if($dungeonInstance->tilehasExit($dungeonInstance->getCurrentExplorersPosition()) === true){
            $dungeonInstance->setStatus(DungeonInstance::DUNGEON_STATUS_TERMINATION);
            
            $xpWonAmount = ceil((($dungeonInstance->getDungeon()->getMinMonsterLevel() + $dungeonInstance->getDungeon()->getMaxMonsterLevel()) / 2) * 8);

            foreach ($dungeonInstance->getExplorers() as $explorer) {
                $explorer->gainXp($xpWonAmount);
                $explorer->getTimers()->setLastDungeon(new DateTime());
            }

            $this->em->flush();

            return new JsonResponse([
                'flavourText' => "Votre équipe est sorti du donjon avec un air fier, vous avez mené a bien cet exploration de donjon ! +{$xpWonAmount} XP"
            ], 200);
        }
        else {
            return new JsonResponse([
                'message' => "Aucune intéraction possible ici."
            ], 400);
        }
    }

    #[Route('/api/dungeon/instance/leave', name: 'api_dungeon_instance_leave')]
    public function leaveDungeonInstance(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        if($this->character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $this->character->getCurrentExplorationDungeonInstance();

        if($dungeonInstance->getStatus() === DungeonInstance::DUNGEON_STATUS_PREPARATION){
            $dungeonInstance->removeExplorer($this->user->getCharacter());

            if(count($dungeonInstance->getExplorers()) < 1){
                $this->deleteDungeonInstance($this->em, $dungeonInstance);
            }
            else if($this->user->getCharacter() === $dungeonInstance->getLeader()){
                $dungeonInstance->setLeader($dungeonInstance->getExplorers()[0]);
            }

            $this->em->flush();
        }
        else if($dungeonInstance->getStatus() === DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            $dungeonInstance->removeExplorer($this->user->getCharacter());

            if(count($dungeonInstance->getExplorers()) < 1){
                $this->deleteDungeonInstance($this->em, $dungeonInstance);
            }
            else if($this->user->getCharacter() === $dungeonInstance->getLeader()){
                $dungeonInstance->setLeader($dungeonInstance->getExplorers()[0]);
            }

            $this->user->getCharacter()->getTimers()->setLastDungeon(new DateTime('-12h'));

            $this->em->flush();
        }
        else if($dungeonInstance->getStatus() === DungeonInstance::DUNGEON_STATUS_TERMINATION){
            $dungeonInstance->removeExplorer($this->user->getCharacter());

            if(count($dungeonInstance->getExplorers()) < 1){
                $this->deleteDungeonInstance($this->em, $dungeonInstance);
            }
            
            $this->em->flush();
        }

        return new JsonResponse([
            'message' => 'Character leaved the dungeon instance sucessfully.'
        ], 200);
    }

    #[Route('/api/dungeon/instance/enter', name: 'api_dungeon_instance_enter')]
    public function enterDungeonInstance(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
        }

        $isValid = $this->verifiesUserAndCharacter($post);

        if(!is_bool($isValid)){
            return $isValid;
        }

        if($this->character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $this->character->getCurrentExplorationDungeonInstance();

        if(!$this->hasCharacterInDungeon($this->user, $dungeonInstance) || $this->user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Character is not the leader.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_PREPARATION){
            return new JsonResponse([
                'message' => "DungeonInstance is not in preparation phase."
            ], 400);
        }

        $dungeonInstance->setStatus(DungeonInstance::DUNGEON_STATUS_EXPLORATION);

        $this->em->flush();

        return new JsonResponse([
            'message' => "Dungeon entered."
        ], 200);
    }

    /* -------------------------------------------------------------------------- */
    /*                              SERVICE FUNCTIONS                             */
    /* -------------------------------------------------------------------------- */

    private function verifiesUserAndCharacter($post): bool|JsonResponse
    {
        $this->user = $this->userRepository->findOneBy(['discordTag' => $post->data->discordUserId]);

        if($this->user === null){
            return new JsonResponse(['message' => 'User does not exist'], 400);
        }

        $this->character = $this->user->getCharacter();

        if($this->character === null){
            return new JsonResponse(['message' => 'User does not have Character'], 400);
        }

        return true;
    }

    private function hasCharacterInDungeon(User $user, DungeonInstance $dungeonInstance): bool
    {
        $character = $user->getCharacter();

        if(in_array($character, $dungeonInstance->getExplorers())){
            return true;
        }

        return false;
    }

    private function deleteDungeonInstance(EntityManagerInterface $em, DungeonInstance $dungeonInstance)
    {
        foreach ($dungeonInstance->getFights() as $combatLog) {
            $combatLog->setDungeonInstance(null);
        }

        $dungeonInstance->setLeader(null);
        $this->em->remove($dungeonInstance);
        $this->em->flush();
    }
}
