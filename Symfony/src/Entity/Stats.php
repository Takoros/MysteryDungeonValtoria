<?php

namespace App\Entity;

use App\Repository\StatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatsRepository::class)]
class Stats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $vitality = null;

    #[ORM\Column]
    private ?int $strength = null;

    #[ORM\Column]
    private ?int $stamina = null;

    #[ORM\Column]
    private ?int $power = null;

    #[ORM\Column]
    private ?int $bravery = null;

    #[ORM\Column]
    private ?int $presence = null;

    #[ORM\Column]
    private ?int $impassiveness = null;

    #[ORM\Column]
    private ?int $agility = null;

    #[ORM\Column]
    private ?int $coordination = null;

    #[ORM\Column]
    private ?int $speed = null;

    #[ORM\Column]
    private ?int $actionPoint = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVitality(): ?int
    {
        return $this->vitality;
    }

    public function setVitality(int $vitality): self
    {
        $this->vitality = $vitality;

        return $this;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): self
    {
        $this->strength = $strength;

        return $this;
    }

    public function getStamina(): ?int
    {
        return $this->stamina;
    }

    public function setStamina(int $stamina): self
    {
        $this->stamina = $stamina;

        return $this;
    }

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(int $power): self
    {
        $this->power = $power;

        return $this;
    }

    public function getBravery(): ?int
    {
        return $this->bravery;
    }

    public function setBravery(int $bravery): self
    {
        $this->bravery = $bravery;

        return $this;
    }

    public function getPresence(): ?int
    {
        return $this->presence;
    }

    public function setPresence(int $presence): self
    {
        $this->presence = $presence;

        return $this;
    }

    public function getImpassiveness(): ?int
    {
        return $this->impassiveness;
    }

    public function setImpassiveness(int $impassiveness): self
    {
        $this->impassiveness = $impassiveness;

        return $this;
    }

    public function getAgility(): ?int
    {
        return $this->agility;
    }

    public function setAgility(int $agility): self
    {
        $this->agility = $agility;

        return $this;
    }

    public function getCoordination(): ?int
    {
        return $this->coordination;
    }

    public function setCoordination(int $coordination): self
    {
        $this->coordination = $coordination;

        return $this;
    }

    public function getSpeed(): ?int
    {
        return $this->speed;
    }

    public function setSpeed(int $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getActionPoint(): ?int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(int $actionPoint): self
    {
        $this->actionPoint = $actionPoint;

        return $this;
    }
}
