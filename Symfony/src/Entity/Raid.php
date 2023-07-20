<?php

namespace App\Entity;

use App\Repository\RaidRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaidRepository::class)]
class Raid
{
    #[ORM\Id]
    #[ORM\Column]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'Raids')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $Area = null;

    #[ORM\Column]
    private ?int $enterMinLevel = null;

    #[ORM\Column]
    private ?int $roomNumbers = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private array $rooms = [];

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

    public function getArea(): ?Area
    {
        return $this->Area;
    }

    public function setArea(?Area $Area): self
    {
        $this->Area = $Area;

        return $this;
    }

    public function getEnterMinLevel(): ?int
    {
        return $this->enterMinLevel;
    }

    public function setEnterMinLevel(int $enterMinLevel): self
    {
        $this->enterMinLevel = $enterMinLevel;

        return $this;
    }

    public function getRoomNumbers(): ?int
    {
        return $this->roomNumbers;
    }

    public function setRoomNumbers(int $roomNumbers): self
    {
        $this->roomNumbers = $roomNumbers;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRooms(): array
    {
        return $this->rooms;
    }

    public function setRooms(array $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }
}
