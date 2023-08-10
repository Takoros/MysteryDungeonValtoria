<?php

namespace App\Controller;

use App\Entity\CombatLog;
use App\Entity\User;
use App\Repository\CombatLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CombatController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/jeu/combat/{id}', name: 'app_combat')]
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

        return $this->render('Combat/combatLog.html.twig', [
            'displayableLogs' => $combatLog->getDisplayableLogs(),
            'combatLog' => $combatLog
        ]);
    }

    private function hasCharacterInCombatLog(User $user, CombatLog $combatLog): bool
    {
        $character = $user->getCharacter();

        if($combatLog->getCharacters()->contains($character)){
            return true;
        }

        return false;
    }
}
