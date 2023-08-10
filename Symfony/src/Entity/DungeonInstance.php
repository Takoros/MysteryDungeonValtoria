<?php

namespace App\Entity;

use App\Repository\AttackRepository;
use App\Repository\DungeonInstanceRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use App\Service\Combat\Arena;
use App\Service\Dungeon\DungeonGenerationService;
use App\Service\Dungeon\MonsterCharacterGenerationService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\KernelInterface;

#[ORM\Entity(repositoryClass: DungeonInstanceRepository::class)]
class DungeonInstance
{
    const DUNGEON_STATUS_PREPARATION = 'dungeon_status_preparation';
    const DUNGEON_STATUS_EXPLORATION = 'dungeon_status_exploration';
    const DUNGEON_STATUS_TERMINATION = 'dungeon_status_termination';

    const MOVE_DIRECTION_UP = 'up';
    const MOVE_DIRECTION_DOWN = 'down';
    const MOVE_DIRECTION_LEFT = 'left';
    const MOVE_DIRECTION_RIGHT = 'right';

    const MAX_NUMBERS_OF_EXPLORERS = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column]
    private array $content = [];

    #[ORM\ManyToOne(inversedBy: 'DungeonInstances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dungeon $Dungeon = null;

    #[ORM\OneToMany(mappedBy: 'currentExplorationDungeonInstance', targetEntity: Character::class)]
    private Collection $Explorers;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Character $leader = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $currentExplorersPosition = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'dungeonInstance', targetEntity: CombatLog::class)]
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

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDungeon(): ?Dungeon
    {
        return $this->Dungeon;
    }

    public function setDungeon(?Dungeon $Dungeon): self
    {
        $this->Dungeon = $Dungeon;

        return $this;
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
        if(count($this->Explorers) >= self::MAX_NUMBERS_OF_EXPLORERS){
            return $this;
        }

        if (!$this->Explorers->contains($explorer)) {
            $this->Explorers->add($explorer);
            $explorer->setCurrentExplorationDungeonInstance($this);
        }

        return $this;
    }

    public function removeExplorer(Character $explorer): self
    {
        if ($this->Explorers->removeElement($explorer)) {
            // set the owning side to null (unless already changed)
            if ($explorer->getCurrentExplorationDungeonInstance() === $this) {
                $explorer->setCurrentExplorationDungeonInstance(null);
            }
        }

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

    public function getCurrentExplorersPosition(): ?string
    {
        return $this->currentExplorersPosition;
    }

    public function setCurrentExplorersPosition(?string $currentExplorersPosition): self
    {
        $this->currentExplorersPosition = $currentExplorersPosition;

        return $this;
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
    /*                            EXPLORATION FUNCTIONS                           */
    /* -------------------------------------------------------------------------- */

    public function moveExplorers(string $direction, EntityManagerInterface $em): bool
    {
        $dungeonGenerationService = new DungeonGenerationService();

        if(!$this->getStatus() === self::DUNGEON_STATUS_EXPLORATION){
            return false;
        }
        
        if($dungeonGenerationService->willHitEdgeWallsOfMap($direction, $this->currentExplorersPosition, $this->content['dungeon']) || $this->tilehasMonsters($this->currentExplorersPosition)){
            return false;
        }

        $this->currentExplorersPosition = $dungeonGenerationService->move($direction, $this->currentExplorersPosition);
        
        $this->modifyDungeonTile($this->currentExplorersPosition, 'isExplorated', DungeonGenerationService::DUNGEON_TILE_EXPLORATED);
        
        $em->flush();

        return true;
    }

    public function fightCurrentPositionMonsters(SpeciesRepository $speciesRepository, TypeRepository $typeRepository, AttackRepository $attackRepository, EntityManager $em)
    {
        if(!$this->tilehasMonsters($this->currentExplorersPosition)){
            return false;
        }

        $monsterCharacterGenerationService = new MonsterCharacterGenerationService($speciesRepository, $typeRepository, $attackRepository);
        $monsters = $monsterCharacterGenerationService->generateMonstersForTile($this->getTileMonsters($this->currentExplorersPosition), $this->getDungeon());

        $arena = new Arena($this->getExplorers(), $monsters, Arena::TYPE_PVP, $attackRepository);
        $arena->launchBattle();
        $arena->combatLog->setLocation($this->getDungeon()->getName());
        $arena->combatLog->setDungeonInstance($this);

        foreach ($this->getExplorers() as $explorer) {
            $arena->combatLog->addCharacter($explorer);
        }

        $arena->combatLog->saveCombatLog($em);

        if($arena->combatLog->getWinner() === 1){
            $xpWonAmount = 0;
            
            foreach ($monsters as $monster) {
                $xpWonAmount += $monster->getLevel() * 1.5;
            }

            $xpWonAmount = ceil($xpWonAmount);

            foreach ($this->getExplorers() as $explorer) {
                $explorer->gainXp($xpWonAmount);
            }

            $this->modifyDungeonTile($this->currentExplorersPosition, 'monsters', null);

            $arena->combatLog->setDungeonCombatMessage($xpWonAmount);
            
            $em->flush();

            return [
                'victory' => true,
                'combatLogId' => $arena->combatLog->getId(),
                'flavourText' => "Votre équipe à vaincu les pokémons sauvages qui s'étaient mis sur votre chemin."
            ];
        }
        else {
            $xpWonAmount = ceil((($this->Dungeon->getMinMonsterLevel() + $this->Dungeon->getMaxMonsterLevel()) / 2) * 5);

            foreach ($this->getExplorers() as $explorer) {
                $explorer->gainXp($xpWonAmount);
            }

            $this->setStatus(self::DUNGEON_STATUS_TERMINATION);

            $arena->combatLog->setDungeonCombatMessage($xpWonAmount);

            $em->flush();

            return [
                'victory' => false,
                'combatLogId' => $arena->combatLog->getId(),
                'flavourText' => "Les pokémons sauvages ont vaincu votre équipe, vous vous êtes enfui en courant du donjon."
            ];
        }
    }

    private function modifyDungeonTile($position, $key, $newValue){
        $dungeonContent = $this->getContent();

        $dungeonContent['dungeon'][$position][$key] = $newValue;

        $this->setContent($dungeonContent);
    }

    /**
     * Returns true if the position tile has some monsters in it.
     */
    private function tilehasMonsters($position): bool
    {
        if(array_key_exists('monsters', $this->getContent()['dungeon'][$position]) && $this->getContent()['dungeon'][$position]['monsters'] !== null){
            return true;
        }

        return false;
    }

    /**
     * Returns true if the position tile is the exit
     */
    public function tilehasExit($position): bool
    {
        if($this->getContent()['dungeon'][$position]['box'] === DungeonGenerationService::DUNGEON_BOX_EXIT){
            return true;
        }

        return false;
    }

    /**
     * Returns the monsters of a tile
     */
    private function getTileMonsters($position): array
    {
        if(array_key_exists('monsters', $this->getContent()['dungeon'][$position])){
            return $this->getContent()['dungeon'][$position]['monsters'];
        }

        return [];
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
            $fight->setDungeonInstance($this);
        }

        return $this;
    }

    public function removeFight(CombatLog $fight): self
    {
        if ($this->fights->removeElement($fight)) {
            // set the owning side to null (unless already changed)
            if ($fight->getDungeonInstance() === $this) {
                $fight->setDungeonInstance(null);
            }
        }

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                            INVITE CODE FUNCTIONS                           */
    /* -------------------------------------------------------------------------- */

    public function generateRandomInviteCode(DungeonInstanceRepository $dungeonInstanceRepository): self
    {
        $isInviteCodeValid = false;

        while ($isInviteCodeValid === false) {
            $currentCode = $this->generateRandomString();

            $raidInstanceCode = $dungeonInstanceRepository->findOneBy(['inviteCode' => $currentCode]);

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
