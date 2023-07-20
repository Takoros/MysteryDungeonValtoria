<?php

namespace App\Entity;

use App\Repository\TimersRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Date;

#[ORM\Entity(repositoryClass: TimersRepository::class)]
class Timers
{
    const TIMERS_TYPE_DUNGEON = 'timers_type_dungeon';
    const TIMERS_COOLDOWN_DUNGEON = 720;

    const MAX_RAID_CHARGES = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastDungeon = null;

    #[ORM\Column]
    private ?int $raidCharges = null;

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

    public function getRaidCharges(): ?int
    {
        return $this->raidCharges;
    }

    public function setRaidCharges(int $raidCharges): self
    {
        $this->raidCharges = $raidCharges;

        return $this;
    }

    public function addOneRaidCharge(): self
    {
        if($this->raidCharges >= self::MAX_RAID_CHARGES){
            return $this;
        }

        $this->raidCharges++;

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

    /* -------------------------------------------------------------------------- */
    /*                              CHARGES FUNCTION                              */
    /* -------------------------------------------------------------------------- */

    public function getCooldownRaidTime(): DateTimeImmutable|null
    {
        if($this->getRaidCharges() >= self::MAX_RAID_CHARGES){
            return null;
        }

        $nextTuesday = strtotime('next Tuesday 18:00'); // Timestamp du prochain Mardi à 18h00
        $nextSaturday = strtotime('next Saturday 18:00'); // Timestamp du prochain Samedi à 18h00
    
        // Si le prochain Mardi est plus proche du jour actuel que le prochain Samedi
        if ($nextTuesday < $nextSaturday) {
            $date = date('Y-m-d H:i:s', $nextTuesday); // Formatage de la date en Y-m-d H:i:s
        } else {
            $date = date('Y-m-d H:i:s', $nextSaturday); // Formatage de la date en Y-m-d H:i:s
        }

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date);
    }
}
