<?php

namespace App\Controller;

use App\Repository\AttackRepository;
use App\Repository\CharacterRepository;
use App\Service\Combat\Arena;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(CharacterRepository $characterRepository, AttackRepository $attackRepository): Response
    {
        $char1 = $characterRepository->find(3); // Takoros
        $char2 = $characterRepository->find(4); // Lameterra
        $char3 = $characterRepository->find(5); // Stratios
        $char4 = $characterRepository->find(6); // Kumiho

        $arena = new Arena([$char1, $char2],[$char3, $char4], Arena::TYPE_PVP, $attackRepository);
        $arena->launchBattle();

        return $this->render('test.html.twig', [
            'logs' => $arena->combatLog->getLogContent()
        ]);
    }
}
