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
    const MAX_RAID_CHARGES = 3;
    const MAX_DUNGEON_CHARGES = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $raidCharges = null;

    #[ORM\Column]
    private ?int $dungeonCharges = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDungeonCharges(): ?int
    {
        return $this->dungeonCharges;
    }

    public function setDungeonCharges(int $dungeonCharges): self
    {
        $this->dungeonCharges = $dungeonCharges;

        return $this;
    }

    public function addOneDungeonCharge(): self
    {
        if($this->dungeonCharges >= self::MAX_DUNGEON_CHARGES){
            return $this;
        }

        $this->dungeonCharges++;

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                              CHARGES FUNCTION                              */
    /* -------------------------------------------------------------------------- */

    public function getCooldownRaidTime(): DateTimeImmutable|null
    {
        if($this->getRaidCharges() >= self::MAX_RAID_CHARGES){
            return null;
        }
        
        $todayDate = new DateTime();
        $nextTuesday = strtotime('next Tuesday 18:00'); // Timestamp du prochain Mardi à 18h00
        $nextSaturday = strtotime('next Saturday 18:00'); // Timestamp du prochain Samedi à 18h00
        
        // Si le prochain Mardi est plus proche du jour actuel que le prochain Samedi
        if($todayDate->format('G') < 18 && ($todayDate->format('l') === 'Tuesday' || $todayDate->format('l') === 'Saturday')){
            $todayTimeStamp = strtotime('today 6pm');
            $date = date('Y-m-d H:i:s', $todayTimeStamp); // Formatage de la date en Y-m-d H:i:s
        }
        else if ($nextTuesday < $nextSaturday) {
            $date = date('Y-m-d H:i:s', $nextTuesday); // Formatage de la date en Y-m-d H:i:s
        } else {
            $date = date('Y-m-d H:i:s', $nextSaturday); // Formatage de la date en Y-m-d H:i:s
        }

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date);
    }

    public function getCooldownDungeonTime(): DateTimeImmutable|null
    {
        if($this->getDungeonCharges() >= self::MAX_RAID_CHARGES){
            return null;
        }

        $todayDate = new DateTime();
        $todayDateTimeStamp = $todayDate->getTimestamp();
        $today06h = strtotime('today 06:00');
        $today18h = strtotime('today 18:00');
        $tomorrow06h = strtotime('tomorrow 06:00');

        if($todayDateTimeStamp > $today18h){
            $date = date('Y-m-d H:i:s', $tomorrow06h); // Formatage de la date en Y-m-d H:i:s
        }
        else if($todayDateTimeStamp > $today06h && $todayDateTimeStamp < $today18h){
            $date = date('Y-m-d H:i:s', $today18h); // Formatage de la date en Y-m-d H:i:s
        }
        else if($todayDateTimeStamp < $today06h){
            $date = date('Y-m-d H:i:s', $today06h); // Formatage de la date en Y-m-d H:i:s
        }

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date);
    }
}
