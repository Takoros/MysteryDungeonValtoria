<?php

namespace App\Entity;

use App\Repository\AttackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttackRepository::class)]
class Attack
{
    const ACTION_TYPE_OFFENSIVE = 'action-type-offensive';
    const ACTION_TYPE_DEFENSIVE = 'action-type-defensive';
    const ACTION_TYPE_UTILITY = 'action-type-utility';
    const ACTION_TYPE_SUPPORTIVE = 'action-type-supportive';

    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private ?string $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $power = null;

    #[ORM\Column(nullable: true)]
    private ?float $statusPower = null;

    #[ORM\Column]
    private ?int $criticalPower = null;

    #[ORM\Column]
    private ?int $actionPointCost = null;

    #[ORM\Column(length: 255)]
    private ?string $scope = null;

    #[ORM\ManyToOne(inversedBy: 'Attacks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $Type = null;

    #[ORM\ManyToMany(targetEntity: Character::class, mappedBy: 'Attacks')]
    private Collection $Characters;

    #[ORM\Column]
    private ?int $levelRequired = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $attackTree = null;

    #[ORM\Column(length: 255)]
    private ?string $actionType = null;

    public function __construct()
    {
        $this->Characters = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPower(): ?float
    {
        return $this->power;
    }

    public function setPower(float $power): self
    {
        $this->power = $power;

        return $this;
    }

    public function getStatusPower(): ?float
    {
        return $this->statusPower;
    }

    public function setStatusPower(?float $statusPower): self
    {
        $this->statusPower = $statusPower;

        return $this;
    }

    public function getCriticalPower(): ?int
    {
        return $this->criticalPower;
    }

    public function setCriticalPower(int $criticalPower): self
    {
        $this->criticalPower = $criticalPower;

        return $this;
    }

    public function getActionPointCost(): ?int
    {
        return $this->actionPointCost;
    }

    public function setActionPointCost(int $actionPointCost): self
    {
        $this->actionPointCost = $actionPointCost;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->Type;
    }

    public function setType(?Type $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->Characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->Characters->contains($character)) {
            $this->Characters->add($character);
            $character->addAttack($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        if ($this->Characters->removeElement($character)) {
            $character->removeAttack($this);
        }

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getLevelRequired(): ?int
    {
        return $this->levelRequired;
    }

    public function setLevelRequired(int $levelRequired): self
    {
        $this->levelRequired = $levelRequired;

        return $this;
    }

    public function getAttackTree(): ?Type
    {
        return $this->attackTree;
    }

    public function setAttackTree(?Type $attackTree): self
    {
        $this->attackTree = $attackTree;

        return $this;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }
}
