<?php

namespace App\Entity;

use App\Repository\DungeonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DungeonRepository::class)]
class Dungeon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $json = null;

    #[ORM\ManyToOne(inversedBy: 'Dungeons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $Area = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getJson(): ?string
    {
        return $this->json;
    }

    public function setJson(?string $json): self
    {
        $this->json = $json;

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
}
