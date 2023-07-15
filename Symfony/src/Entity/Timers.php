<?php

namespace App\Entity;

use App\Repository\TimersRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimersRepository::class)]
class Timers
{
    const TIMERS_TYPE_DUNGEON = 'timers_type_dungeon';
    const TIMERS_COOLDOWN_DUNGEON = 720;

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

    /* -------------------------------------------------------------------------- */
    /*                               CHECK FUNCTIONS                              */
    /* -------------------------------------------------------------------------- */

    public function canEnterDungeon(): bool
    {
        if($this->lastDungeon === null){
            return true;
        }
        else if(new DateTime() > $this->getCooldownDateTime($this->lastDungeon, self::TIMERS_TYPE_DUNGEON)){
            return true;
        }

        return false;
    }

    public function getCooldownDungeonTime(): DateTimeImmutable|null 
    {
        if($this->lastDungeon === null || new DateTime() > $this->getCooldownDateTime($this->lastDungeon, self::TIMERS_TYPE_DUNGEON)){
            return null;
        }

        return $this->getCooldownDateTime($this->lastDungeon, self::TIMERS_TYPE_DUNGEON);
    }

    public function getCooldownDateTime($lastDateTime, $type): DateTimeImmutable
    {   
        $dateTimeImmutable = new DateTimeImmutable();
        $dateTimeImmutable = $dateTimeImmutable->createFromMutable($lastDateTime);

        if($type === self::TIMERS_TYPE_DUNGEON){
            return $dateTimeImmutable->modify('+ 720 minute');
        }
    }
}
