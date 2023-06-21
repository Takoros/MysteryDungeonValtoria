<?php

namespace App\Entity;

use App\Repository\CombatLogRepository;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatusInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CombatLogRepository::class)]
class CombatLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logs = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $teamOne = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $teamTwo = [];

    #[ORM\Column(nullable: true)]
    private ?int $winner = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToMany(targetEntity: Character::class, inversedBy: 'CombatLogs')]
    private Collection $Characters;

    public function __construct()
    {
        $this->Characters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogs(): ?string
    {
        return $this->logs;
    }

    public function setLogs(?string $logs): self
    {
        $this->logs = $logs;

        return $this;
    }

    public function getTeamOne(): array
    {
        return $this->teamOne;
    }

    public function setTeamOne(?array $teamOne): self
    {
        $this->teamOne = $teamOne;

        return $this;
    }

    public function getTeamTwo(): array
    {
        return $this->teamTwo;
    }

    public function setTeamTwo(?array $teamTwo): self
    {
        $this->teamTwo = $teamTwo;

        return $this;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner(?int $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->Characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->Characters->contains($character)) {
            $this->Characters->add($character);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        $this->Characters->removeElement($character);

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                                  IN COMBAT                                 */
    /* -------------------------------------------------------------------------- */

    private $logContent = [];

    const HAS_DODGED = 'has_dodged';

    public function getLogContent(): array
    {
        return $this->logContent;
    }

    public function addStringLog(string $string): void
    {
        $this->logContent[] = $string;
    }

    /**
     * Creates a log for the use of an attack
     */
    public function addUseAttackLog(Fighter $caster, Attack $attack, bool $hasCrit): void
    {
        $useAttackLog = "{$caster->getName()} lance {$attack->getName()} !";

        if($hasCrit){
            $useAttackLog .= " Critique !";
        }

        $this->logContent[] = $useAttackLog;
    }

    /**
     * Creates a log for receiving damage
     */
    public function addReceiveDamageLog(int|string $damageInflicted, Fighter $target): void
    {
        if($damageInflicted === self::HAS_DODGED){
            $this->logContent[] = "{$target->getName()} a esquivé l'attaque' !";
            return ;
        }

        $this->logContent[] = "{$target->getName()} subit {$damageInflicted} points de dégâts.";
            
        if($target->isKO()){
            $this->logContent[] = "{$target->getName()} est tombé K.O.";
        }
        else {
            $this->logContent[] = "{$target->getName()} a ({$target->getCurrentVitality()}/{$target->getVitality()}) HP restant.";
        }    

    }

    /**
     * Creates a log for receiving damage from a status
     */
    public function addReceiveDamageFromStatusLog(int $statusDamage, DamagingStatus $status, Fighter $target): void
    {
        $this->logContent[] = "{$target->getName()} subit {$statusDamage} points de dégâts de son/sa ". $this->translateStatusName($status->getDamagingType());

        if($target->isKO()){
            $this->logContent[] = "{$target->getName()} est tombé K.O.";
        }
        else {
            $this->logContent[] = "{$target->getName()} a ({$target->getCurrentVitality()}/{$target->getVitality()}) HP restant.";
        }   
    }

    /**
     * Creates a log for receiving healing
     */
    public function addReceiveHealingLog(int $healingReceived, Fighter $target): void
    {
        $this->logContent[] = "{$target->getName()} est soigné de {$healingReceived} points de vitalité.";
        $this->logContent[] = "{$target->getName()} a ({$target->getCurrentVitality()}/{$target->getVitality()}) HP restant.";
    }

    /**
     * Creates a log receiving a status
     */
    public function addReceiveStatusLog($status, $target){
        if($status->getStatusType() === StatusInterface::TYPE_BUFF){
            $this->addBuffStatusLog($status, $target);
        }

        if($status->getStatusType() === StatusInterface::TYPE_NERF){
            $this->addNerfStatusLog($status, $target);
        }

        if($status->getStatusType() === StatusInterface::TYPE_CONTROL){
            $this->addControlStatusLog($status, $target);
        }

        if($status->getStatusType() === StatusInterface::TYPE_DAMAGING){
            $this->addDamagingStatusLog($status, $target);
        }
    }

    /**
     * Creates a log for the expiration of a Statistic Modifier Status
     */
    public function addStatisticModifierStatusLossLog($fighter, $status): void
    {
        if($status->getStatusType() === StatusInterface::TYPE_BUFF){
            $this->logContent[] = "{$fighter->getName()} perd son bonus de {$status->getModifier()} en {$this->translateStatisticName($status->getStatisticModified())}";

            return ;
        }

        $this->logContent[] = "{$fighter->getName()} perd son malus de {$status->getModifier()} en {$this->translateStatisticName($status->getStatisticModified())}";
    }

    /**
     * Creates a log for the expiration of a Control Status
     */
    public function addControlStatusLossLog($fighter, $status): void
    {
        $this->logContent[] = "{$fighter->getName()} perd le status {$this->translateStatusName($status->getControlType())}";
    }

    private function addBuffStatusLog($status, Fighter $target): void
    {
        $this->logContent[] = "{$target->getName()} voit son/sa {$this->translateStatisticName($status->getStatisticModified())} augmenter de {$status->getModifier()} (Actuel : {$target->getStatisticTotal($status->getStatisticModified())})";
    }
    
    private function addNerfStatusLog($status, Fighter $target): void
    {
        $this->logContent[] = "{$target->getName()} voit son/sa {$this->translateStatisticName($status->getStatisticModified())} diminuer de {$status->getModifier()} (Actuel : {$target->getStatisticTotal($status->getStatisticModified())})";
    }

    private function addControlStatusLog($status, Fighter $target): void
    {
        $this->logContent[] = "{$target->getName()} subit le status {$this->translateStatusName($status->getControlType())}";
    }

    public function addDamagingStatusLog($status, Fighter $fighter): void
    {
        $this->logContent[] = "{$fighter->getName()} subit le status {$this->translateStatusName($status->getDamagingType())}";
    }

    /**
     * Creates a log for the expiration of a damaging Status
     */
    public function addDamagingStatusLossLog($status, Fighter $fighter): void
    {
        $this->logContent[] = "{$fighter->getName()} perd le status {$this->translateStatusName($status->getDamagingType())}";
    }

    /**
     * Returns the french name of a statistic
     */
    private function translateStatisticName($statistic): string
    {
        if($statistic === 'vitality'){
            return 'vitalité';
        }

        if($statistic === 'strength'){
            return 'force';
        }

        if($statistic === 'stamina'){
            return 'endurance';
        }

        if($statistic === 'power'){
            return 'pouvoir';
        }

        if($statistic === 'bravery'){
            return 'courage';
        }

        if($statistic === 'presence'){
            return 'présence';
        }

        if($statistic === 'impassiveness'){
            return 'impassibilité';
        }

        if($statistic === 'agility'){
            return 'agilité';
        }

        if($statistic === 'coordination'){
            return $statistic;
        }

        if($statistic === 'speed'){
            return 'vitesse';
        }
    }

    /**
     * Returns the french name of a status
     */
    private function translateStatusName($status): string
    {
        switch ($status) {
            case ControlStatus::CONTROL_PARALYSIS:
                return 'Paralysie';
                break;
            case ControlStatus::CONTROL_FREEZE:
                return 'Gel';
                break;
            case ControlStatus::CONTROL_SLEEP:
                return 'Sommeil';
                break;
            case ControlStatus::CONTROL_CONFUSION:
                return 'Confusion';
                break;
            case ControlStatus::CONTROL_PETRIFICATION:
                return 'Pétrification';
                break;
            case ControlStatus::CONTROL_FATIGUE:
                return 'Fatigue';
                break;
            case ControlStatus::CONTROL_YAWN:
                return 'Baillement';
                break;
            case DamagingStatus::DAMAGING_BURN:
                return 'Brûlure';
                break;
            case DamagingStatus::DAMAGING_POISON:
                return 'Empoisonnement';
                break;
            case DamagingStatus::DAMAGING_BAD_POISON:
                return 'Empoisonnement Grave';
                break;
        }
    }
}
