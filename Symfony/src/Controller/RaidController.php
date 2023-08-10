<?php

namespace App\Controller;

use App\Entity\RaidInstance;
use App\Entity\User;
use App\Form\CreateRaidInstanceType;
use App\Form\JoinRaidInstanceType;
use App\Repository\AttackRepository;
use App\Repository\RaidInstanceRepository;
use App\Repository\RaidRepository;
use App\Service\Raid\RaidDataManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class RaidController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/jeu/raid', name: 'app_raid')]
    public function raid(Request $request, RaidDataManager $raidDataManager, RaidInstanceRepository $raidInstanceRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $raidInstance = $user->getCharacter()->getCurrentExplorationRaidInstance();

        $joinRaidInstanceForm = $this->createForm(JoinRaidInstanceType::class);

        $joinRaidInstanceForm->handleRequest($request);

        if($joinRaidInstanceForm->isSubmitted() && $joinRaidInstanceForm->isValid() && $user->getCharacter()->getTimers()->getRaidCharges() > 0){
            $formData = $joinRaidInstanceForm->getData();

            $raidInstance = $raidInstanceRepository->findOneBy(['inviteCode' => $formData['inviteCode']]);

            if($raidInstance !== null){
                $raidInstance->addExplorer($user->getCharacter());

                $em->flush();
                return $this->redirectToRoute('app_raid');
            }
        }

        if($raidInstance !== null){
            $raidDataManager->setRaidId($raidInstance->getRaid()->getId());
        }

        return $this->render('Raid/raid.html.twig', [
            'raidInstance' => $raidInstance,
            'raidDataManager' => $raidDataManager,
            'joinRaidInstanceView' => $joinRaidInstanceForm->createView()
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/jeu/raid/create', name: 'app_raid_create')]
    public function create(Request $request, RaidRepository $raidRepository, RaidInstanceRepository $raidInstanceRepository, TranslatorInterface $translator, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $character = $user->getCharacter();

        if(!$character->getTimers()->getRaidCharges() > 0){
            return $this->redirectToRoute('app_raid');
        }

        $raidInstanceCreateForm = $this->createForm(CreateRaidInstanceType::class, null, [
            'character' => $user->getCharacter(),
            'raidRepository' => $raidRepository,
            'translator' => $translator
        ]);

        $raidInstanceCreateForm->handleRequest($request);

        if($raidInstanceCreateForm->isSubmitted() && $raidInstanceCreateForm->isValid()){
            $formData = $raidInstanceCreateForm->getData();

            $raidInstance = new RaidInstance();
            $raidInstance->setDateCreated(new DateTime())
                         ->setRaid($formData['Raid'])
                         ->setCurrentExplorersRoom(0)
                         ->setLeader($character)
                         ->addExplorer($character)
                         ->setStatus(RaidInstance::RAID_STATUS_PREPARATION)
                         ->generateRandomInviteCode($raidInstanceRepository);
            
            $em->persist($raidInstance);
            $em->flush();

            return $this->redirectToRoute('app_raid');
        }

        return $this->render('Raid/raid-create.html.twig', [
            'raidInstanceCreateFormView' => $raidInstanceCreateForm->createView()
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/jeu/raid/instance/{id}/enter', name: 'app_raid_instance_enter')]
    public function enterInstance($id, RaidInstanceRepository $raidInstanceRepository, EntityManagerInterface $em): JsonResponse
    {
        $raidInstance = $raidInstanceRepository->find($id);

        if($raidInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }

        $user = $this->getUser();

        if(!$this->hasCharacterInRaid($user, $raidInstance) || $user->getCharacter() !== $raidInstance->getLeader()){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
        }

        if($raidInstance->getStatus() !== RaidInstance::RAID_STATUS_PREPARATION){
            return new JsonResponse([
                'message' => "Vous n'êtes pas en préparation"
            ], 400);
        }

        $raidInstance->explorerEnterRaid($em);

        return new JsonResponse([
            'message' => 'Vous êtes bien entré dans le raid.'
        ], 200);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/jeu/raid/instance/{id}/leave', name: 'app_raid_instance_leave')]
    public function leaveInstance($id, RaidInstanceRepository $raidInstanceRepository, EntityManagerInterface $em): JsonResponse
    {
        $raidInstance = $raidInstanceRepository->find($id);
    
        if($raidInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }

        $user = $this->getUser();

        if(!$this->hasCharacterInRaid($user, $raidInstance)){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
        }

        $raidInstance->explorerLeaveRaid($user->getCharacter(), $em);

        $em->flush();

        return new JsonResponse([
            'message' => 'Vous avez bien quitté le donjon.'
        ], 200);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/jeu/raid/instance/{id}/fight', name: 'app_raid_instance_fight')]
    public function fightMonsterInstance($id, RaidInstanceRepository $raidInstanceRepository, AttackRepository $attackRepository, RaidDataManager $raidDataManager, EntityManagerInterface $em): JsonResponse
    {
        $raidInstance = $raidInstanceRepository->find($id);
    
        if($raidInstance === null){
            return new JsonResponse([
                'message' => 'Instance non valide.'
            ], 400);
        }

        $user = $this->getUser();

        if(!$this->hasCharacterInRaid($user, $raidInstance)){
            return new JsonResponse([
                'message' => 'Accès non-autorisé.'
            ], 400);
        }

        if($raidInstance->getStatus() !== RaidInstance::RAID_STATUS_EXPLORATION){
            return new JsonResponse([
                'message' => "Vous n'êtes pas en exploration"
            ], 400);
        }

        $raidDataManager->setRaidId($raidInstance->getRaid()->getId());
        $raidInstance->explorerFightMonsters($em, $attackRepository, $raidDataManager);
        
        return new JsonResponse([
            'message' => 'Vous avez bien combattu les monstres.'
        ], 200);
    }

    /* -------------------------------------------------------------------------- */
    /*                              SERVICE FUNCTIONS                             */
    /* -------------------------------------------------------------------------- */

    private function hasCharacterInRaid(User $user, RaidInstance $raidInstance): bool
    {
        $character = $user->getCharacter();

        if(in_array($character, $raidInstance->getExplorers())){
            return true;
        }

        return false;
    }
}
