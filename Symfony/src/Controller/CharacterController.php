<?php

namespace App\Controller;

use App\Form\CreateCharacterType;
use App\Form\ModifyDescriptionType;
use App\Form\ModifyRotationType;
use App\Repository\AttackRepository;
use App\Repository\CharacterRepository;
use App\Repository\SpeciesRepository;
use App\Service\CharacterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CharacterController extends AbstractController
{
    #[Route('/personnage/creation', name: 'app_character_create')]
    public function create(Request $request, SpeciesRepository $speciesRepository, CharacterService $characterService): Response
    {
        $createCharacterForm = $this->createForm(CreateCharacterType::class, null, [
            'speciesRepository' => $speciesRepository
        ]);

        $createCharacterForm->handleRequest($request);

        if($createCharacterForm->isSubmitted() && $createCharacterForm->isValid()){
            $user = $this->getUser();
            $characterService->persistNewCharacter($createCharacterForm->getData(), $user);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('Character/create.html.twig', [
            'createCharacterFormView' => $createCharacterForm->createView()
        ]);
    }

    #[Route('/jeu/personnage', name: 'app_character')]
    #[Route('/jeu/personnage/{id}', name:'app_character_other_view')]
    public function show(int $id = null, Request $request, CharacterRepository $characterRepository, EntityManagerInterface $em): Response
    {
        if($id === null){
            $user = $this->getUser();
            $character = $user->getCharacter();
            $isSelfCharacter = true;
        }
        else {
            $isSelfCharacter = false;
            $character = $characterRepository->find($id);

            if($character === null){
                return $this->redirectToRoute('app_home');
            }
        }

        $modifyDescriptionForm = $this->createForm(ModifyDescriptionType::class, $character);
        $modifyDescriptionForm->handleRequest($request);

        if($modifyDescriptionForm->isSubmitted() && $modifyDescriptionForm->isValid() && $id === null){
            $em->flush();

            return $this->redirectToRoute('app_character');
        }

        return $this->render('Character/show.html.twig', [
            'character' => $character,
            'modifyDescriptionFormView' => $modifyDescriptionForm->createView(),
            'isSelfCharacter' => $isSelfCharacter
        ]);
    }

    #[Route('/jeu/personnage/modification/rotation/{type}', name: 'app_character_modify_rotation')]
    public function modify_rotation(String $type, Request $request, CharacterService $characterService, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if($type === 'Opener'){
            $rotationToModify = $user->getCharacter()->getOpenerRotation();
        }
        else if ($type === 'Rotation') {
            $rotationToModify = $user->getCharacter()->getRotation();
        }
        else {
            throw new Exception("Argument incorrect");
        }

        $modifyRotationForm = $this->createForm(ModifyRotationType::class, $rotationToModify, [
            'character' => $user->getCharacter(),
            'characterService' => $characterService
        ]);

        $modifyRotationForm->handleRequest($request);

        if($modifyRotationForm->isSubmitted() && $modifyRotationForm->isValid()){
            $newRotation = $modifyRotationForm->getData();

            if($newRotation->getActionPointUsed() <= $user->getCharacter()->getStats()->getActionPoint()){
                $em->flush();

                return $this->redirectToRoute('app_character');
            }
            else {
                return $this->redirectToRoute('app_character_modify_rotation', [
                    'type' => $type
                ]);
            }
        }

        return $this->render('Character/modify_rotation.html.twig', [
            'character' => $user->getCharacter(),
            'type' => $type,
            'modifyRotationFormView' => $modifyRotationForm->createView()
        ]);
    }

    #[Route('/jeu/personnage/attaques-disponibles', name: 'app_character_attacks', priority:1)]
    public function attacks(AttackRepository $attackRepository): Response
    {
        return $this->render('Character/attacks.html.twig', [
            'allAttacks' => $attackRepository->findAll()
        ]);
    }

    /* -------------------------------------------------------------------------- */
    /*                               JS FETCH CALLS                               */
    /* -------------------------------------------------------------------------- */

    #[Route('/jeu/personnage/spendPoint', name: 'app_character_spend_point', priority:1)]
    public function spendPoint(Request $request, CharacterService $characterService): JsonResponse
    {
        $post = json_decode($request->getContent());
        $user = $this->getUser();

        $results = $characterService->spendStatPoint($user->getCharacter(), $post->data->statToIncrease, 1);

        return new JsonResponse($results);
    }

    #[Route('/jeu/personnage/data-attack', name: 'app_character_data-attack', priority:1)]
    public function getAttackData(Request $request, AttackRepository $attackRepository): JsonResponse
    {
        $post = json_decode($request->getContent());

        $attack = $attackRepository->find($post->data->attackId);

        if($attack === null){
            return new JsonResponse('Attaque introuvable', 500);
        }

        return new JsonResponse([
            'attack' => [
                'name' => $attack->getName(),
                'actionPointCost' => $attack->getActionPointCost(),
                'attackPower' => $attack->getPower(),
                'attackStatusPower' => $attack->getStatusPower(),
                'attackCriticalPower' => $attack->getCriticalPower(),
                'attackDescription' => $attack->getDescription()
            ]
        ]);
    }
}
