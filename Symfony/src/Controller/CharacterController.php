<?php

namespace App\Controller;

use App\Entity\ItemTypeEnum;
use App\Form\CreateCharacterType;
use App\Form\ModifyDescriptionType;
use App\Form\ModifyRotationType;
use App\Repository\AttackRepository;
use App\Repository\CharacterRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use App\Service\CharacterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CharacterController extends AbstractController
{
    #[Route('/personnage/creation', name: 'app_character_create')]
    public function create(Request $request, SpeciesRepository $speciesRepository, AttackRepository $attackRepository, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $createCharacterForm = $this->createForm(CreateCharacterType::class, null, [
            'speciesRepository' => $speciesRepository,
            'translator' => $translator
        ]);

        $createCharacterForm->handleRequest($request);

        if($createCharacterForm->isSubmitted() && $createCharacterForm->isValid()){
            $user = $this->getUser();
            $createCharacterForm->getData()->createNewCharacter($user, $attackRepository, $em, $translator);

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
    public function modify_rotation(String $type, Request $request, TranslatorInterface $translator, TypeRepository $typeRepository, AttackRepository $attackRepository, EntityManagerInterface $em): Response
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
            'availableAttacks' => $user->getCharacter()->getAvailableAttacks($typeRepository, $attackRepository),
            'translator' => $translator
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

    #[Route('/jeu/personnage/unequip/{type}', name: 'app_character_unequip', priority:1)]
    public function unequip(string $type, EntityManagerInterface $em): Response
    {
        $character = $this->getUser()->getCharacter();
        $gear = $character->getGear();
        $type = ItemTypeEnum::from($type);

        match($type){
            ItemTypeEnum::ITEM_TYPE_WEAPON => $item = $gear->getWeapon(),
            ItemTypeEnum::ITEM_TYPE_SCARF => $item = $gear->getScarf(),
            ItemTypeEnum::ITEM_TYPE_ACCESSORY => $item = $gear->getAccessory(),
        };

        if($item !== null){
            $gear->unequip($type);
            $em->flush();
        }

        return $this->redirectToRoute('app_character');
    }

    #[Route('/jeu/personnage/equip/{id}', name: 'app_character_equip', priority:1)]
    public function equip(int $id, TranslatorInterface $translator, EntityManagerInterface $em): Response
    {
        $character = $this->getUser()->getCharacter();
        $inventory = $character->getInventory();
        $gear = $character->getGear();
        $items = $inventory->getItems();

        foreach ($items as $item) {
            if($item->getId() === $id){
                $success = $gear->equip($item);
                if($success){
                    $inventory->removeItem($item);
                    $em->flush();
                }
                else {
                    $this->addFlash('danger', $translator->trans('vous_ne_remplissez_pas_les_conditions_pour_equiper_cet_objet', [], 'app'));
                }
            }
        }

        return $this->redirectToRoute('app_character');
    }

    /* -------------------------------------------------------------------------- */
    /*                               JS FETCH CALLS                               */
    /* -------------------------------------------------------------------------- */

    #[Route('/jeu/personnage/spendPoint', name: 'app_character_spend_point', priority:1)]
    public function spendPoint(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): JsonResponse
    {
        $post = json_decode($request->getContent());
        $user = $this->getUser();

        try {
            $results = $user->getCharacter()->getStats()->spendStatPoint($post->data->statToIncrease, $translator);

            $em->flush();
        } catch (\Exception $e) {
            $results = [
                'statusCode' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }

        return new JsonResponse($results);
    }

    #[Route('/jeu/personnage/data-attack', name: 'app_character_data-attack', priority:1)]
    public function getAttackData(Request $request, AttackRepository $attackRepository, TranslatorInterface $translator): JsonResponse
    {
        $post = json_decode($request->getContent());

        $attack = $attackRepository->find($post->data->attackId);

        if($attack === null){
            return new JsonResponse('Attaque introuvable', 500);
        }

        $attackName = strtolower($attack->getName());
        
        $attackName = str_replace(' ', '_', $attackName);
        $attackName = str_replace('ô', 'o', $attackName);
        $attackName = str_replace('â', 'a', $attackName);
        $attackName = str_replace('û', 'u', $attackName);
        $attackName = str_replace('é', 'e', $attackName);
        $attackName = str_replace('è', 'e', $attackName);
        $attackName = str_replace('ç', 'c', $attackName);

        return new JsonResponse([
            'attack' => [
                'name' => $translator->trans($attackName.'_attack', [], 'app'),
                'actionPointCost' => $attack->getActionPointCost(),
                'attackPower' => $attack->getPower(),
                'attackStatusPower' => $attack->getStatusPower(),
                'attackCriticalPower' => $attack->getCriticalPower(),
                'attackDescription' => $translator->trans($attackName.'_attack_description', [], 'app'),
            ]
        ]);
    }
}
