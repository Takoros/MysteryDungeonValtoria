<?php

namespace App\Controller;

use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/jeu', name: 'app_hub')]
    public function hub(): Response
    {
        $user = $this->getUser();

        if($user->getCharacter() === null){
            return $this->redirectToRoute('app_character_create');
        }

        return $this->render('Main/hub.html.twig');
    }

    #[Route('/jeu/classement', name: 'app_ranking')]
    public function ranking(CharacterRepository $characterRepository): Response
    {
        $allCharacters = $characterRepository->findAllOrderedForRanking();

        return $this->render('Main/ranking.html.twig',[
            'allCharacters' => $allCharacters
        ]);
    }
}
