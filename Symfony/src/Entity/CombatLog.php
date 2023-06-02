<?php

namespace App\Entity;

use App\Repository\CombatLogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CombatLogRepository::class)]
class CombatLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $logs = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $teamOne = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $teamTwo = [];

    #[ORM\Column(nullable: true)]
    private ?int $winner = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToMany(targetEntity: Character::class, inversedBy: 'CombatLogs')]
    private Collection $Characters;

    public function __construct()
    {
        $this->Characters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogs(): ?string
    {
        return $this->logs;
    }

    public function setLogs(?string $logs): self
    {
        $this->logs = $logs;

        return $this;
    }

    public function getTeamOne(): array
    {
        return $this->teamOne;
    }

    public function setTeamOne(?array $teamOne): self
    {
        $this->teamOne = $teamOne;

        return $this;
    }

    public function getTeamTwo(): array
    {
        return $this->teamTwo;
    }

    public function setTeamTwo(?array $teamTwo): self
    {
        $this->teamTwo = $teamTwo;

        return $this;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner(?int $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

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
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        $this->Characters->removeElement($character);

        return $this;
    }
}
