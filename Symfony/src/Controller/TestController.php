<?php

namespace App\Controller;

use App\Entity\CombatLog;
use App\Entity\Dungeon;
use App\Entity\DungeonInstance;
use App\Repository\AttackRepository;
use App\Repository\CharacterRepository;
use App\Repository\CombatLogRepository;
use App\Repository\DungeonInstanceRepository;
use App\Repository\DungeonRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use App\Service\Combat\Arena;
use App\Service\Dungeon\DungeonGenerationService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(CharacterRepository $characterRepository, AttackRepository $attackRepository, EntityManagerInterface $em): Response
    {
        $char1 = $characterRepository->find(3); // Takoros
        $char2 = $characterRepository->find(4); // Lameterra
        $char3 = $characterRepository->find(5); // Stratios
        $char4 = $characterRepository->find(6); // Kumiho

        $arena = new Arena([$char1, $char2],[$char3, $char4], Arena::TYPE_PVP, $attackRepository);
        $arena->launchBattle();

        $arena->combatLog->saveCombatLog($em);

        return $this->render('combatLog.html.twig', [
            'displayableLogs' => $arena->combatLog->getDisplayableLogs(),
            'combatLog' => $arena->combatLog
        ]);
    }

    #[Route('/test2', name: 'app_test2')]
    public function test2(CombatLogRepository $combatLogRepository): Response
    {
        $combatLog = $combatLogRepository->find(8);

        return $this->render('combatLog.html.twig', [
            'displayableLogs' => $combatLog->getDisplayableLogs(),
            'combatLog' => $combatLog
        ]);
    }

    #[Route('/test3', name: 'app_test3')]
    public function test3(DungeonGenerationService $dungeonGenerationService, DungeonRepository $dungeonRepository, CharacterRepository $characterRepository, EntityManagerInterface $em)
    {
        $dungeonOne = $dungeonRepository->find('DUNGEON_ONE');

        $generatedDungeon = $dungeonGenerationService->generateDungeon($dungeonOne, DungeonGenerationService::DUNGEON_SIZE_SMALL);
        $tako = $characterRepository->find(1);
        $pomme = $characterRepository->find(2);

        $dungeonInstance = new DungeonInstance();
        $dungeonInstance->setContent($generatedDungeon['content'])
                        ->setDateCreated(new DateTime())
                        ->setDungeon($dungeonOne)
                        ->setCurrentExplorersPosition($generatedDungeon['currentExplorersPosition'])
                        ->setLeader($tako)
                        ->addExplorer($tako)
                        ->addExplorer($pomme)
                        ->setStatus(DungeonInstance::DUNGEON_STATUS_PREPARATION);
                        
        $em->persist($dungeonInstance);
        $em->flush();

        dd('toto');
    }

    #[Route('/test4', name: 'app_test4')]
    public function test4(DungeonInstanceRepository $dungeonInstanceRepository, EntityManagerInterface $em)
    {
        $dungeonInstance = $dungeonInstanceRepository->find(5);

        $dungeonInstance->moveExplorers(DungeonInstance::MOVE_DIRECTION_RIGHT, $em);

        return $this->render('dungeon.html.twig', [
            'dungeon' => $dungeonInstance->getContent()['dungeon'],
            'data' => $dungeonInstance->getContent()['data'],
            'currentExplorersPosition' => $dungeonInstance->getCurrentExplorersPosition()
        ]);
    }

    #[Route('/test5', name: 'app_test5')]
    public function test5(DungeonInstanceRepository $dungeonInstanceRepository, SpeciesRepository $speciesRepository, TypeRepository $typeRepository, AttackRepository $attackRepository, EntityManagerInterface $em)
    {
        $dungeonInstance = $dungeonInstanceRepository->find(5);

        $dungeonInstance->fightCurrentPositionMonsters($speciesRepository, $typeRepository, $attackRepository, $em);

        return $this->redirectToRoute('app_test4');
    }
}