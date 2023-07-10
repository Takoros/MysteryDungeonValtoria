<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Entity\Character;
use App\Entity\DungeonInstance;
use App\Service\Dungeon\DungeonGenerationService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DungeonAPIController extends AbstractAPIController
{
    /* ARGS */
    public array $API_DUNGEON_CHECK_ENTER_VALIDITY_ARGS = ["discordUserId"];
    public array $API_DUNGEON_INSTANCE_JOIN_ARGS = ["discordUserId", "leaderDiscordUserId"];
    public array $API_DUNGEON_INSTANCE_SHOW_ARGS = ["discordUserId"];
    public array $API_DUNGEON_INSTANCE_MEMBERS_ARGS = ["discordUserId"];
    public array $API_DUNGEON_INSTANCE_CREATE_ARGS = ["discordUserId", "dungeonName"];
    public array $API_DUNGEON_INSTANCE_MOVE_ARGS = ["discordUserId", "direction"];
    public array $API_DUNGEON_INSTANCE_FIGHT_ARGS = ["discordUserId"];
    public array $API_DUNGEON_INSTANCE_INTERACT_ARGS = ["discordUserId"];
    public array $API_DUNGEON_INSTANCE_LEAVE_ARGS = ["discordUserId"];
    public array $API_DUNGEON_INSTANCE_ENTER_ARGS = ["discordUserId"];

    #[Route('/api/dungeon/check/enter-validity', name: 'api_dungeon_check_enter_validity')]
    public function isAbleToEnterDungeon(): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        if($character->getCurrentExplorationDungeonInstance() !== null){
            return new JsonResponse(['result' => false], 200);
        }

        return new JsonResponse(['result' => $character->getTimers()->canEnterDungeon()], 200);
    }

    #[Route('/api/dungeon/instance/join', name: 'api_dungeon_instance_join')]
    public function joinDungeonInstance(EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $leaderUser = $this->userRepository->findOneBy(['discordTag' => $this->post->data->leaderDiscordUserId]);

        if($leaderUser === null || $leaderUser->getCharacter() === null){
            return new JsonResponse(['message' => 'ID de leader incorrect.'], 400);
        }

        $dungeonInstance = $leaderUser->getCharacter()->getCurrentExplorationDungeonInstance();

        if($character->getTimers()->canEnterDungeon() && $character->getCurrentExplorationDungeonInstance() === null){
            $dungeonInstance->addExplorer($character);
            $em->flush();

            return new JsonResponse(['message' => 'Le personnage à bien rejoint le groupe.'], 200);
        }
        else {
            return new JsonResponse(['message' => 'Le personnage ne peut pas entrer dans le groupe car il se trouve déjà dans un donjon, ou est épuisé.'], 400);
        }
    }

    #[Route('/api/dungeon/instance/show', name: 'api_dungeon_instance_show')]
    public function showDungeonInstance()
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }
        
        if($character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => "Vous n'êtes dans aucun donjon."], 400);
        }

        $dungeonInstance = $character->getCurrentExplorationDungeonInstance();

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
    public function getDungeonInstanceMembers()
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }
        
        if($character->getCurrentExplorationDungeonInstance() === null){
            return new JsonResponse(['message' => 'Character is not in a dungeon.'], 400);
        }

        $dungeonInstance = $character->getCurrentExplorationDungeonInstance();
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
    public function createDungeonInstance(DungeonGenerationService $dungeonGenerationService, EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $dungeon = $this->apiGetDungeonByName($this->post->data->dungeonName);

        if(get_class($dungeon) === JsonResponse::class){
            return $dungeon;
        }

        if($character->getCurrentExplorationDungeonInstance() !== null){
            return new JsonResponse(['message' => 'Vous êtes déjà dans un donjon.'], 400);
        }

        if(!$character->getTimers()->canEnterDungeon()){
            return new JsonResponse(['message' => 'Vous avez besoin de vous reposer, attendez avant de retourner explorer les donjons.'], 400);
        }

        $generatedDungeon = $dungeonGenerationService->generateDungeon($dungeon, $dungeon->getSize());
        $dungeonInstance = new DungeonInstance();
        $dungeonInstance->setContent($generatedDungeon['content'])
                        ->setDateCreated(new DateTime())
                        ->setDungeon($dungeon)
                        ->setCurrentExplorersPosition($generatedDungeon['currentExplorersPosition'])
                        ->setLeader($character)
                        ->addExplorer($character)
                        ->setStatus(DungeonInstance::DUNGEON_STATUS_PREPARATION);

        $em->persist($dungeonInstance);
        $em->flush();

        return new JsonResponse(['message' => 'Instance de donjon crée avec succès !'], 200);
    }

    #[Route('/api/dungeon/instance/move', name: 'api_dungeon_instance_move')]
    public function explorersMoveDungeonInstance(EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $allowedDirections = [DungeonInstance::MOVE_DIRECTION_DOWN, DungeonInstance::MOVE_DIRECTION_UP, DungeonInstance::MOVE_DIRECTION_LEFT, DungeonInstance::MOVE_DIRECTION_RIGHT];

        if(!in_array($this->post->data->direction, $allowedDirections)){
            return new JsonResponse([
                'message' => 'Direction invalide.'
            ], 400);
        }
        
        $dungeonInstance = $this->apiGetDungeonInstanceByCharacter($character);
        
        if($dungeonInstance === null){
            return $dungeonInstance;
        }

        if($character !== $dungeonInstance->getLeader()){
            return $this->getJsonResponseForError(self::ERROR_CHARACTER_NOT_LEADER_DUNGEON_INSTANCE);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return $this->getJsonResponseForError(self::ERROR_DUNGEON_INSTANCE_NOT_EXPLORATION_PHASE);
        }

        $hasMoved = $dungeonInstance->moveExplorers($this->post->data->direction, $em);

        if($hasMoved === false){
            return new JsonResponse([
                'message' => 'Mouvement impossible.'
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Mouvement effectué.'
        ], 200);
    }

    #[Route('/api/dungeon/instance/fight', name: 'api_dungeon_instance_fight')]
    public function fightMonstersDungeonInstance(EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $dungeonInstance = $this->apiGetDungeonInstanceByCharacter($character);
        
        if($dungeonInstance === null){
            return $dungeonInstance;
        }

        if($character !== $dungeonInstance->getLeader()){
            return $this->getJsonResponseForError(self::ERROR_CHARACTER_NOT_LEADER_DUNGEON_INSTANCE);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return $this->getJsonResponseForError(self::ERROR_DUNGEON_INSTANCE_NOT_EXPLORATION_PHASE);
        }

        $data = $dungeonInstance->fightCurrentPositionMonsters($this->speciesRepository, $this->typeRepository, $this->attackRepository, $em);

        if($data === false){
            return $this->getJsonResponseForError(self::ERROR_DUNGEON_INSTANCE_FIGHT_NO_MONSTERS);
        }

        return new JsonResponse([
            'combatLogUrl' => $this->generateUrl('app_combat', ['id' => $data['combatLogId']], UrlGeneratorInterface::ABSOLUTE_URL),
            'message' => 'Combat effectué.',
            'victory' => $data['victory']
        ], 200);
    }

    #[Route('/api/dungeon/instance/interact', name: 'api_dungeon_instance_interact')]
    public function interactWithDungeonInstance(EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $dungeonInstance = $this->apiGetDungeonInstanceByCharacter($character);
        
        if($dungeonInstance === null){
            return $dungeonInstance;
        }

        if($character !== $dungeonInstance->getLeader()){
            return $this->getJsonResponseForError(self::ERROR_CHARACTER_NOT_LEADER_DUNGEON_INSTANCE);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return $this->getJsonResponseForError(self::ERROR_DUNGEON_INSTANCE_NOT_EXPLORATION_PHASE);
        }

        if($dungeonInstance->tilehasExit($dungeonInstance->getCurrentExplorersPosition()) === true){
            $dungeonInstance->setStatus(DungeonInstance::DUNGEON_STATUS_TERMINATION);
            
            $xpWonAmount = ceil((($dungeonInstance->getDungeon()->getMinMonsterLevel() + $dungeonInstance->getDungeon()->getMaxMonsterLevel()) / 2) * 8);

            foreach ($dungeonInstance->getExplorers() as $explorer) {
                $explorer->gainXp($xpWonAmount);
                $explorer->getTimers()->setLastDungeon(new DateTime());
            }

            $em->flush();

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
    public function leaveDungeonInstance(EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $dungeonInstance = $this->apiGetDungeonInstanceByCharacter($character);
        
        if($dungeonInstance === null){
            return $dungeonInstance;
        }

        if($dungeonInstance->getStatus() === DungeonInstance::DUNGEON_STATUS_PREPARATION){
            $dungeonInstance->removeExplorer($user->getCharacter());

            if(count($dungeonInstance->getExplorers()) < 1){
                $this->deleteDungeonInstance($em, $dungeonInstance);
            }
            else if($user->getCharacter() === $dungeonInstance->getLeader()){
                $dungeonInstance->setLeader($dungeonInstance->getExplorers()[0]);
            }

            $em->flush();
        }
        else if($dungeonInstance->getStatus() === DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            $dungeonInstance->removeExplorer($user->getCharacter());

            if(count($dungeonInstance->getExplorers()) < 1){
                $this->deleteDungeonInstance($em, $dungeonInstance);
            }
            else if($user->getCharacter() === $dungeonInstance->getLeader()){
                $dungeonInstance->setLeader($dungeonInstance->getExplorers()[0]);
            }

            $user->getCharacter()->getTimers()->setLastDungeon(new DateTime('-12h'));

            $em->flush();
        }
        else if($dungeonInstance->getStatus() === DungeonInstance::DUNGEON_STATUS_TERMINATION){
            $dungeonInstance->removeExplorer($user->getCharacter());

            if(count($dungeonInstance->getExplorers()) < 1){
                $this->deleteDungeonInstance($em, $dungeonInstance);
            }
            else if($user->getCharacter() === $dungeonInstance->getLeader()){
                $dungeonInstance->setLeader($dungeonInstance->getExplorers()[0]);
            }
            
            $em->flush();
        }

        return new JsonResponse([
            'message' => 'Character leaved the dungeon instance sucessfully.'
        ], 200);
    }

    #[Route('/api/dungeon/instance/enter', name: 'api_dungeon_instance_enter')]
    public function enterDungeonInstance(EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $dungeonInstance = $this->apiGetDungeonInstanceByCharacter($character);
        
        if($dungeonInstance === null){
            return $dungeonInstance;
        }

        if($character !== $dungeonInstance->getLeader()){
            return $this->getJsonResponseForError(self::ERROR_CHARACTER_NOT_LEADER_DUNGEON_INSTANCE);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_PREPARATION){
            return $this->getJsonResponseForError(self::ERROR_DUNGEON_INSTANCE_NOT_PREPARATION_PHASE);
        }

        $dungeonInstance->setStatus(DungeonInstance::DUNGEON_STATUS_EXPLORATION);

        $em->flush();

        return new JsonResponse([
            'message' => "Dungeon entered."
        ], 200);
    }

    /* -------------------------------------------------------------------------- */
    /*                              SERVICE FUNCTIONS                             */
    /* -------------------------------------------------------------------------- */

    private function deleteDungeonInstance(EntityManagerInterface $em, DungeonInstance $dungeonInstance)
    {
        foreach ($dungeonInstance->getFights() as $combatLog) {
            $combatLog->setDungeonInstance(null);
        }

        $dungeonInstance->setLeader(null);
        $em->remove($dungeonInstance);
        $em->flush();
    }
}
