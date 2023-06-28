<?php

namespace App\Entity;

use App\Repository\TimersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimersRepository::class)]
class Timers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastDungeon = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastDungeon(): ?\DateTimeInterface
    {
        return $this->lastDungeon;
    }

    public function setLastDungeon(?\DateTimeInterface $lastDungeon): self
    {
        $this->lastDungeon = $lastDungeon;

        return $this;
    }
}
