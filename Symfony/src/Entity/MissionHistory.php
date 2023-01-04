<?php

namespace App\Entity;

use App\Repository\MissionHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MissionHistoryRepository::class)]
class MissionHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ssPlusRankCompleted = null;

    #[ORM\Column]
    private ?int $ssRankCompleted = null;

    #[ORM\Column]
    private ?int $sRankCompleted = null;

    #[ORM\Column]
    private ?int $aRankCompleted = null;

    #[ORM\Column]
    private ?int $bRankCompleted = null;

    #[ORM\Column]
    private ?int $cRankCompleted = null;

    #[ORM\Column]
    private ?int $dRankCompleted = null;

    #[ORM\Column]
    private ?int $eRankCompleted = null;

    #[ORM\ManyToOne(inversedBy: 'MissionHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $Character = null;

    #[ORM\ManyToOne(inversedBy: 'MissionHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Guild $Guild = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSsPlusRankCompleted(): ?int
    {
        return $this->ssPlusRankCompleted;
    }

    public function setSsPlusRankCompleted(int $ssPlusRankCompleted): self
    {
        $this->ssPlusRankCompleted = $ssPlusRankCompleted;

        return $this;
    }

    public function getSsRankCompleted(): ?int
    {
        return $this->ssRankCompleted;
    }

    public function setSsRankCompleted(int $ssRankCompleted): self
    {
        $this->ssRankCompleted = $ssRankCompleted;

        return $this;
    }

    public function getSRankCompleted(): ?int
    {
        return $this->sRankCompleted;
    }

    public function setSRankCompleted(int $sRankCompleted): self
    {
        $this->sRankCompleted = $sRankCompleted;

        return $this;
    }

    public function getARankCompleted(): ?int
    {
        return $this->aRankCompleted;
    }

    public function setARankCompleted(int $aRankCompleted): self
    {
        $this->aRankCompleted = $aRankCompleted;

        return $this;
    }

    public function getBRankCompleted(): ?int
    {
        return $this->bRankCompleted;
    }

    public function setBRankCompleted(int $bRankCompleted): self
    {
        $this->bRankCompleted = $bRankCompleted;

        return $this;
    }

    public function getCRankCompleted(): ?int
    {
        return $this->cRankCompleted;
    }

    public function setCRankCompleted(int $cRankCompleted): self
    {
        $this->cRankCompleted = $cRankCompleted;

        return $this;
    }

    public function getDRankCompleted(): ?int
    {
        return $this->dRankCompleted;
    }

    public function setDRankCompleted(int $dRankCompleted): self
    {
        $this->dRankCompleted = $dRankCompleted;

        return $this;
    }

    public function getERankCompleted(): ?int
    {
        return $this->eRankCompleted;
    }

    public function setERankCompleted(int $eRankCompleted): self
    {
        $this->eRankCompleted = $eRankCompleted;

        return $this;
    }

    public function getCharacter(): ?Character
    {
        return $this->Character;
    }

    public function setCharacter(?Character $Character): self
    {
        $this->Character = $Character;

        return $this;
    }

    public function getGuild(): ?Guild
    {
        return $this->Guild;
    }

    public function setGuild(?Guild $Guild): self
    {
        $this->Guild = $Guild;

        return $this;
    }
}
