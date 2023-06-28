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

    public function __construct()
    {
        $this->Explorers = new ArrayCollection();
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

    /* -------------------------------------------------------------------------- */
    /*                            EXPLORATION FUNCTIONS                           */
    /* -------------------------------------------------------------------------- */

    public function moveExplorers(string $direction, EntityManagerInterface $em): self|bool
    {
        $dungeonGenerationService = new DungeonGenerationService();
        
        if($dungeonGenerationService->willHitEdgeWallsOfMap($direction, $this->currentExplorersPosition, $this->content['dungeon']) || $this->tilehasMonsters($this->currentExplorersPosition)){
            return false;
        }

        $this->currentExplorersPosition = $dungeonGenerationService->move($direction, $this->currentExplorersPosition);
        
        $this->modifyDungeonTile($this->currentExplorersPosition, 'isExplorated', DungeonGenerationService::DUNGEON_TILE_EXPLORATED);
        
        $em->flush();

        return $this;
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

            $em->flush();

            return [
                'victory' => true,
                'message' => "Votre équipe à vaincu les pokémons sauvages qui s'étaient mis sur votre chemin."
            ];
        }
        else {
            $xpWonAmount = ceil((($this->Dungeon->getMinMonsterLevel() + $this->Dungeon->getMaxMonsterLevel()) / 2) * 5);

            foreach ($this->getExplorers() as $explorer) {
                $explorer->setCurrentExplorationDungeonInstance(null);
                $explorer->gainXp($xpWonAmount);
                $explorer->getTimers()->setLastDungeon(new DateTime());
            }

            $this->Dungeon = null;
            $this->leader = null;

            $em->remove($this);

            $em->flush();

            return [
                'victory' => false,
                'message' => "Les pokémons sauvages ont vaincu votre équipe, vous vous êtes enfui en courant du donjon."
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
     * Returns the monsters of a tile
     */
    private function getTileMonsters($position): array
    {
        if(array_key_exists('monsters', $this->getContent()['dungeon'][$position])){
            return $this->getContent()['dungeon'][$position]['monsters'];
        }

        return [];
    }
}
