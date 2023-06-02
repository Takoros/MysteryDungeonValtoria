<?php

namespace App\Entity;

use App\Repository\RotationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RotationRepository::class)]
class Rotation
{
    const TYPE_OPENER = 'Opener';
    const TYPE_ROTATION = 'Rotation';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'Rotation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $Character = null;

    #[ORM\ManyToOne]
    private ?Attack $attackOne = null;

    #[ORM\ManyToOne]
    private ?Attack $attackTwo = null;

    #[ORM\ManyToOne]
    private ?Attack $attackThree = null;

    #[ORM\ManyToOne]
    private ?Attack $attackFour = null;

    #[ORM\ManyToOne]
    private ?Attack $attackFive = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getAttackOne(): ?Attack
    {
        return $this->attackOne;
    }

    public function setAttackOne(?Attack $attackOne): self
    {
        $this->attackOne = $attackOne;

        return $this;
    }

    public function getAttackTwo(): ?Attack
    {
        return $this->attackTwo;
    }

    public function setAttackTwo(?Attack $attackTwo): self
    {
        $this->attackTwo = $attackTwo;

        return $this;
    }

    public function getAttackThree(): ?Attack
    {
        return $this->attackThree;
    }

    public function setAttackThree(?Attack $attackThree): self
    {
        $this->attackThree = $attackThree;

        return $this;
    }

    public function getAttackFour(): ?Attack
    {
        return $this->attackFour;
    }

    public function setAttackFour(?Attack $attackFour): self
    {
        $this->attackFour = $attackFour;

        return $this;
    }

    public function getAttackFive(): ?Attack
    {
        return $this->attackFive;
    }

    public function setAttackFive(?Attack $attackFive): self
    {
        $this->attackFive = $attackFive;

        return $this;
    }
}
