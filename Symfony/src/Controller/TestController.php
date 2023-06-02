<?php

namespace App\Controller;

use App\Repository\AttackRepository;
use App\Repository\CharacterRepository;
use App\Service\Combat\Arena;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(CharacterRepository $characterRepository, AttackRepository $attackRepository): Response
    {
        $teamOneCharacter = $characterRepository->find(1); // Takoros
        $teamOneCharacter2 = $characterRepository->find(2);  
        $teamTwoCharacter = $characterRepository->find(3); 
        $teamTwoCharacter2 = $characterRepository->find(4); 

        $arena = new Arena([$teamOneCharacter, $teamOneCharacter2],[$teamTwoCharacter, $teamTwoCharacter2], Arena::TYPE_PVP, $attackRepository);
        $arena->launchBattle();

        return $this->render('test.html.twig', [
            'logs' => $arena->combatLogContent
        ]);
    }
}
