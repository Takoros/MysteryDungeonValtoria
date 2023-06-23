<?php

namespace App\Controller;

use App\Entity\CombatLog;
use App\Repository\AttackRepository;
use App\Repository\CharacterRepository;
use App\Repository\CombatLogRepository;
use App\Service\Combat\Arena;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $combatLog = $combatLogRepository->find(340);

        return $this->render('combatLog.html.twig', [
            'displayableLogs' => $combatLog->getDisplayableLogs(),
            'combatLog' => $combatLog
        ]);
    }
}