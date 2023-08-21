<?php

namespace App\Entity;

use App\Repository\AttackRepository;
use App\Repository\RotationRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Routing\Exception\InvalidParameterException;

#[ORM\Entity(repositoryClass: RotationRepository::class)]
class Rotation
{
    const TYPE_OPENER = 'Opener';
    const TYPE_ROTATION = 'Rotation';

    /** Maximum of action per rotation */
    const MAX_ACTION_PER_ROTATION = 5;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attack $attackOne = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attack $attackTwo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attack $attackThree = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attack $attackFour = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attack $attackFive = null;

    #[ORM\ManyToOne(inversedBy: 'rotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $Character = null;

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

    public function getAllAttacks(): array
    {
        return [$this->getAttackOne(),
                $this->getAttackTwo(),
                $this->getAttackThree(),
                $this->getAttackFour(),
                $this->getAttackFive(),
        ];
    }

    public function getAttack($number): ?Attack
    {
        if($number === 'One'){
            return $this->attackOne;
        }
        else if($number === 'Two'){
            return $this->attackTwo;
        }
        else if($number === 'Three'){
            return $this->attackThree;
        }
        else if($number === 'Four'){
            return $this->attackFour;
        }
        else if($number === 'Five'){
            return $this->attackFive;
        }
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

    public function getCharacter(): ?Character
    {
        return $this->Character;
    }

    public function setCharacter(?Character $Character): self
    {
        $this->Character = $Character;

        return $this;
    }

    public function getSlotAttack(int $slot): Attack|null
    {
        if($slot === 1){
            return $this->getAttackOne();
        }
        else if($slot === 2){
            return $this->getAttackTwo();
        }
        else if($slot === 3){
            return $this->getAttackThree();
        }
        else if($slot === 4){
            return $this->getAttackFour();
        }
        else if($slot === 5){
            return $this->getAttackFive();
        }
        else {
            throw new InvalidParameterException();
        }
    }

    public function setSlotAttack(int $slot, Attack $newAttack): self
    {
        if($slot === 1){
            $this->attackOne = $newAttack;
        }
        else if($slot === 2){
            $this->attackTwo = $newAttack;
        }
        else if($slot === 3){
            $this->attackThree = $newAttack;
        }
        else if($slot === 4){
            $this->attackFour = $newAttack;
        }
        else if($slot === 5){
            $this->attackFive = $newAttack;
        }
        else {
            throw new InvalidParameterException();
        }

        return $this;
    }

    /**
     * Returns the number of action point used by the Rotation
     */
    public function getActionPointUsed(): int
    {
        $actionPointUsed = 0;

        foreach ($this->getAllAttacks() as $Attack) {
            $actionPointUsed += $Attack->getActionPointCost();
        }

        return $actionPointUsed;
    }

    /* -------------------------------------------------------------------------- */
    /*                              ROTATION CREATION                             */
    /* -------------------------------------------------------------------------- */

    public function initNewRotation(string $type, Character $character, AttackRepository $attackRepository): void
    {
        if($type === self::TYPE_OPENER){
            $this->type = self::TYPE_OPENER;
        }
        else if ($type === self::TYPE_ROTATION){
            $this->type = self::TYPE_ROTATION;
        }
        else {
            throw new Exception("Wrong Rotation Type", 500);
        }

        $lutte = $attackRepository->find("ATTACK_EXPLORER_BASE");

        $this->setCharacter($character)
             ->setAttackOne($lutte)
             ->setAttackTwo($lutte)
             ->setAttackThree($lutte)
             ->setAttackFour($lutte)
             ->setAttackFive($lutte);
    }
}
