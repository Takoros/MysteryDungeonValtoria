<?php

namespace App\Controller;

use App\Entity\CombatLog;
use App\Entity\DungeonInstance;
use App\Entity\User;
use App\Form\CreateDungeonInstanceType;
use App\Repository\AttackRepository;
use App\Repository\CombatLogRepository;
use App\Repository\DungeonInstanceRepository;
use App\Repository\DungeonRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use App\Service\Dungeon\DungeonGenerationService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/combat/{id}', name: 'app_combat')]
    public function combatLogShow($id, CombatLogRepository $combatLogRepository): Response
    {
        $combatLog = $combatLogRepository->find($id);

        if($combatLog === null){
            return new JsonResponse([
                'message' => 'Log non valide.'
            ], 400);
        }

        $user = $this->getUser();

        if(!$this->hasCharacterInCombatLog($user, $combatLog)){
            return $this->redirectToRoute('app_home');
        }

        return $this->render('combatLog.html.twig', [
            'displayableLogs' => $combatLog->getDisplayableLogs(),
            'combatLog' => $combatLog
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/donjon', name: 'app_dungeon')]
    public function dungeonShow(): Response
    {
        $user = $this->getUser();
        $dungeonInstance = $user->getCharacter()->getCurrentExplorationDungeonInstance();

        return $this->render('Dungeon/dungeon.html.twig', [
            'dungeonInstance' => $dungeonInstance,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/donjon/create', name: 'app_dungeon_create')]
    public function dungeonCreate(Request $request, DungeonRepository $dungeonRepository, DungeonGenerationService $dungeonGenerationService, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $character = $user->getCharacter();

        if(!$character->getTimers()->canEnterDungeon()){
            return $this->redirectToRoute('app_dungeon');
        }

        $dungeonInstanceCreateForm = $this->createForm(CreateDungeonInstanceType::class, null,[
            'character' => $user->getCharacter(),
            'dungeonRepository' => $dungeonRepository
        ]);

        $dungeonInstanceCreateForm->handleRequest($request);

        if($dungeonInstanceCreateForm->isSubmitted() && $dungeonInstanceCreateForm->isValid()){
            $formData = $dungeonInstanceCreateForm->getData();

            $generatedDungeon = $dungeonGenerationService->generateDungeon($formData['Dungeon'], $formData['Dungeon']->getSize());
            $dungeonInstance = new DungeonInstance();
            $dungeonInstance->setContent($generatedDungeon['content'])
                            ->setDateCreated(new DateTime())
                            ->setDungeon($formData['Dungeon'])
                            ->setCurrentExplorersPosition($generatedDungeon['currentExplorersPosition'])
                            ->setLeader($character)
                            ->addExplorer($character)
                            ->setStatus(DungeonInstance::DUNGEON_STATUS_PREPARATION);

            $em->persist($dungeonInstance);
            $em->flush();
            return $this->redirectToRoute('app_dungeon');
        }

        return $this->render('Dungeon/dungeon-create.html.twig', [
            'dungeonInstanceCreateFormView' => $dungeonInstanceCreateForm->createView()
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/donjon/instance/{id}/move/{direction}', name: 'app_dungeon_instance_move')]
    public function moveInDungeon($id, $direction, DungeonInstanceRepository $dungeonInstanceRepository, EntityManagerInterface $em): JsonResponse
    {
        $allowedDirections = [DungeonInstance::MOVE_DIRECTION_DOWN, DungeonInstance::MOVE_DIRECTION_UP, DungeonInstance::MOVE_DIRECTION_LEFT, DungeonInstance::MOVE_DIRECTION_RIGHT];

        if(!in_array($direction, $allowedDirections)){
            return new JsonResponse([
                'message' => 'Direction non valide.'
            ], 400);
        }

        $dungeonInstance = $dungeonInstanceRepository->find($id);
    
        if($dungeonInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }

        $user = $this->getUser();

        if(!$this->hasCharacterInDungeon($user, $dungeonInstance) || $user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return new JsonResponse([
                'message' => "Vous n'êtes pas en cours d'exploration."
            ], 400);
        }

        $hasMoved = $dungeonInstance->moveExplorers($direction, $em);

        if($hasMoved === false){
            return new JsonResponse([
                'message' => 'Déplacement impossible.'
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Déplacement effectué.'
        ], 200);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/donjon/instance/{id}/fight', name: 'app_dungeon_instance_fight')]
    public function fightMonsters($id, DungeonInstanceRepository $dungeonInstanceRepository, SpeciesRepository $speciesRepository, TypeRepository $typeRepository, AttackRepository $attackRepository, EntityManagerInterface $em): JsonResponse
    {
        $dungeonInstance = $dungeonInstanceRepository->find($id);
    
        if($dungeonInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }
        
        $user = $this->getUser();

        if(!$this->hasCharacterInDungeon($user, $dungeonInstance) || $user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return new JsonResponse([
                'message' => "Vous n'êtes pas en cours d'exploration."
            ], 400);
        }

        $message = $dungeonInstance->fightCurrentPositionMonsters($speciesRepository, $typeRepository, $attackRepository, $em);

        return new JsonResponse([
            $message
        ], 200);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/donjon/instance/{id}/enter', name: 'app_dungeon_instance_enter')]
    public function enterDungeon($id, DungeonInstanceRepository $dungeonInstanceRepository, EntityManagerInterface $em): JsonResponse
    {
        $dungeonInstance = $dungeonInstanceRepository->find($id);
    
        if($dungeonInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }

        $user = $this->getUser();

        if(!$this->hasCharacterInDungeon($user, $dungeonInstance) || $user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_PREPARATION){
            return new JsonResponse([
                'message' => "Vous n'êtes pas en préparation"
            ], 400);
        }

        $message = $dungeonInstance->setStatus(DungeonInstance::DUNGEON_STATUS_EXPLORATION);

        $em->flush();

        return new JsonResponse([
            $message
        ], 200);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/donjon/instance/{id}/interact', name: 'app_dungeon_instance_interact')]
    public function interactDungeon($id, DungeonInstanceRepository $dungeonInstanceRepository, EntityManagerInterface $em): JsonResponse
    {
        $dungeonInstance = $dungeonInstanceRepository->find($id);
    
        if($dungeonInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }
        
        $user = $this->getUser();

        if(!$this->hasCharacterInDungeon($user, $dungeonInstance) || $user->getCharacter() !== $dungeonInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
        }

        if($dungeonInstance->getStatus() !== DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return new JsonResponse([
                'message' => "Vous n'êtes pas en cours d'exploration."
            ], 400);
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

    #[IsGranted('ROLE_USER')]
    #[Route('/donjon/instance/{id}/leave', name: 'app_dungeon_instance_leave')]
    public function leaveDungeon($id, DungeonInstanceRepository $dungeonInstanceRepository, EntityManagerInterface $em): JsonResponse
    {
        $dungeonInstance = $dungeonInstanceRepository->find($id);
    
        if($dungeonInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }

        $user = $this->getUser();

        if(!$this->hasCharacterInDungeon($user, $dungeonInstance)){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
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

        $this->addFlash('success', 'Vous avez bien quitté le donjon.');

        return new JsonResponse([
            'message' => 'Vous avez bien quitté le donjon.'
        ], 200);
    }

    private function hasCharacterInCombatLog(User $user, CombatLog $combatLog): bool
    {
        $character = $user->getCharacter();

        if($combatLog->getCharacters()->contains($character)){
            return true;
        }

        return false;
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
        $em->remove($dungeonInstance);
        $em->flush();
    }
}
