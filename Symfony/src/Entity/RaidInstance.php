<?php

namespace App\Entity;

use App\Repository\AttackRepository;
use App\Repository\RaidInstanceRepository;
use App\Service\Combat\Arena;
use App\Service\Raid\RaidDataManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\JsonResponse;

#[ORM\Entity(repositoryClass: RaidInstanceRepository::class)]
class RaidInstance
{
    const RAID_STATUS_PREPARATION = 'raid_status_preparation';
    const RAID_STATUS_EXPLORATION = 'raid_status_exploration';
    const RAID_STATUS_TERMINATION = 'raid_status_termination';
    const RAID_STATUS_TERMINATION_DEFEAT = 'raid_status_termination_defeat';

    const RAID_MAX_NUMBERS_OF_EXPLORERS = 8;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'currentExplorationRaidInstance', targetEntity: Character::class)]
    private Collection $Explorers;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Raid $Raid = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Character $leader = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $currentExplorersRoom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\OneToMany(mappedBy: 'raidInstance', targetEntity: CombatLog::class)]
    private Collection $fights;

    #[ORM\Column(length: 255)]
    private ?string $inviteCode = null;

    public function __construct()
    {
        $this->Explorers = new ArrayCollection();
        $this->fights = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getExplorers(): array
    {
        $explorers = [];

        foreach ($this->Explorers as $explorer) {
            $explorers[] = $explorer;
        }

        return $explorers;
    }

    public function addExplorer(Character $explorer): self
    {
        if (!$this->Explorers->contains($explorer)) {
            $this->Explorers->add($explorer);
            $explorer->setCurrentExplorationRaidInstance($this);
        }

        return $this;
    }

    public function removeExplorer(Character $explorer): self
    {
        if ($this->Explorers->removeElement($explorer)) {
            // set the owning side to null (unless already changed)
            if ($explorer->getCurrentExplorationRaidInstance() === $this) {
                $explorer->setCurrentExplorationRaidInstance(null);
            }
        }

        return $this;
    }

    public function getRaid(): ?Raid
    {
        return $this->Raid;
    }

    public function setRaid(?Raid $Raid): self
    {
        $this->Raid = $Raid;

        return $this;
    }

    public function getLeader(): ?Character
    {
        return $this->leader;
    }

    public function setLeader(?Character $leader): self
    {
        $this->leader = $leader;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCurrentExplorersRoom(): ?int
    {
        return $this->currentExplorersRoom;
    }

    public function setCurrentExplorersRoom(int $currentExplorersRoom): self
    {
        $this->currentExplorersRoom = $currentExplorersRoom;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return Collection<int, CombatLog>
     */
    public function getFights(): Collection
    {
        return $this->fights;
    }

    public function addFight(CombatLog $fight): self
    {
        if (!$this->fights->contains($fight)) {
            $this->fights->add($fight);
            $fight->setRaidInstance($this);
        }

        return $this;
    }

    public function removeFight(CombatLog $fight): self
    {
        if ($this->fights->removeElement($fight)) {
            // set the owning side to null (unless already changed)
            if ($fight->getRaidInstance() === $this) {
                $fight->setRaidInstance(null);
            }
        }

        return $this;
    }

    public function deleteRaidInstance(EntityManagerInterface $em): void
    {
        foreach ($this->getFights() as $combatLog) {
            $combatLog->setRaidInstance(null);
        }

        $this->setLeader(null);
        $em->remove($this);
    }

    public function getInviteCode(): ?string
    {
        return $this->inviteCode;
    }

    public function setInviteCode(string $inviteCode): self
    {
        $this->inviteCode = $inviteCode;

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                              ROOM DATA GETTERS                             */
    /* -------------------------------------------------------------------------- */

    public function getCurrentRoomData(): array
    {
        $currentRoom = "room-" . strval($this->currentExplorersRoom);

        return $this->Raid->getRooms()[$currentRoom];
    }

    public function getCurrentRoomDescription(): string
    {
        $currentRoomData = $this->getCurrentRoomData();

        return $currentRoomData['description'];
    }

    public function getCurrentRoomXpPerMonsterKOd(): int
    {
        $currentRoomData = $this->getCurrentRoomData();

        return $currentRoomData['xpPerMonsterKOd'];
    }

    public function getCurrentRoomMonsters(RaidDataManager $raidDataManager): array
    {
        $currentRoomData = $this->getCurrentRoomData();

        $currentRoomMonstersId = $currentRoomData['monstersId'];
        $currentRoomMonsters = [];

        foreach ($currentRoomMonstersId as $monsterId) {
            $currentRoomMonsters[] = $raidDataManager->getRaidMonsterFromId($monsterId);
        }

        return $currentRoomMonsters;
    }

    /* -------------------------------------------------------------------------- */
    /*                              SERVICE FUNCTIONS                             */
    /* -------------------------------------------------------------------------- */
    
    public function explorerEnterRaid(EntityManagerInterface $em): void
    {
        $this->setStatus(RaidInstance::RAID_STATUS_EXPLORATION)
             ->setCurrentExplorersRoom(1);

        $explorers = $this->getExplorers();

        foreach ($explorers as $explorer) {
            $explorerTimers = $explorer->getTimers();

            $explorerTimers->setRaidCharges($explorerTimers->getRaidCharges() - 1);
        }

        $em->flush();
    }

    public function explorerLeaveRaid(Character $character, EntityManagerInterface $em): JsonResponse
    {
        $this->removeExplorer($character);

        if(count($this->getExplorers()) < 1){
            $this->deleteRaidInstance($em);
        }
        else if($character === $this->getLeader()){
            $this->setLeader($this->getExplorers()[0]);
        }

        return new JsonResponse([
            'message' => 'Raid quitté avec succès.'
        ], 200);
    }

    public function explorerFightMonsters(EntityManagerInterface $em, AttackRepository $attackRepository, RaidDataManager $raidDataManager): JsonResponse 
    {
        $currentRoomMonsters = $this->getCurrentRoomMonsters($raidDataManager);
        $explorers = $this->getExplorers();

        $arena = new Arena($explorers, $currentRoomMonsters, Arena::TYPE_PVP, $attackRepository);
        $arena->launchBattle();
        $arena->combatLog->setLocation($this->Raid->getName());
        $arena->combatLog->setRaidInstance($this);

        foreach ($this->getExplorers() as $explorer) {
            $arena->combatLog->addCharacter($explorer);
        }

        $arena->combatLog->saveCombatLog($em);
        
        return new JsonResponse($this->manageFightResult($em, $arena->combatLog), 200);
    }

    private function manageFightResult(EntityManagerInterface $em, CombatLog $combatLog): array
    {
        if($combatLog->getWinner() === 1){
            $xpWonAmount = count($combatLog->getTeamTwo()) * $this->getCurrentRoomXpPerMonsterKOd();
            $xpWonAmount = ceil($xpWonAmount);

            foreach ($this->getExplorers() as $explorer) {
                $explorer->gainXp($xpWonAmount);
            }

            $combatLog->setDungeonCombatMessage($xpWonAmount);

            if($this->currentExplorersRoom >= $this->getRaid()->getRoomNumbers()){
                $this->setStatus(self::RAID_STATUS_TERMINATION);
            }
            else {
                $this->currentExplorersRoom++;
            }

            $em->flush();

            return [
                'victory' => true,
                'combatLogId' => $combatLog->getId(),
                'flavourText' => "Votre équipe à vaincu les pokémons sauvages qui s'étaient mis sur votre chemin."
            ];
        }
        else {
            $combatLog->setDungeonCombatMessage(0);
            $this->setStatus(self::RAID_STATUS_TERMINATION_DEFEAT);

            $em->flush();

            return [
                'victory' => false,
                'combatLogId' => $combatLog->getId(),
                'flavourText' => "Vous avez fui du raid en courant suite à votre échec cuisant."
            ];
        }

    }

    /* -------------------------------------------------------------------------- */
    /*                            INVITE CODE FUNCTIONS                           */
    /* -------------------------------------------------------------------------- */

    public function generateRandomInviteCode(RaidInstanceRepository $raidInstanceRepository): self
    {
        $isInviteCodeValid = false;

        while ($isInviteCodeValid === false) {
            $currentCode = $this->generateRandomString();

            $raidInstanceCode = $raidInstanceRepository->findOneBy(['inviteCode' => $currentCode]);

            if($raidInstanceCode === null){
                $isInviteCodeValid = true;
            }
        }

        $this->inviteCode = $currentCode;

        return $this;
    }

    private function generateRandomString(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
