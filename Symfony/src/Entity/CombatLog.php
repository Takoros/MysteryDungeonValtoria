<?php

namespace App\Entity;

use App\Repository\CombatLogRepository;
use App\Service\Combat\Fighter;
use App\Service\Combat\LogLine;
use App\Service\Combat\Status\ControlStatus;
use App\Service\Combat\Status\DamagingStatus;
use App\Service\Combat\Status\StatisticModifierStatus;
use App\Service\Combat\Status\StatusInterface;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array|string $LogLines = [];

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

    public function getLogLines(): array|string
    {
        return $this->LogLines;
    }

    public function addLogLine(LogLine $LogLine): self
    {
        $this->LogLines[] = $LogLine;

        return $this;
    }

    public function serializeLogLines(): self
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $this->LogLines = $serializer->serialize($this->LogLines, 'json');

        return $this;
    }

    public function getDeserializedLogLines(): array
    {
        $serializer = new Serializer(
            [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        );

        return $serializer->deserialize($this->LogLines, LogLine::class.'[]', 'json', []);
    }

    /**
     * Persist the Combat Log into the database
     */
    public function saveCombatLog(EntityManagerInterface $em): void
    {
        $this->serializeLogLines();
        $this->setDateCreation($dateTime = new DateTime());

        $this->setWinner($this->arena->winner);

        $em->persist($this);
        $em->flush();
    }

    /**
     * Returns the formatted logs for display
     */
    public function getDisplayableLogs(): array
    {
        $displayableLogs = [];

        foreach ($this->getDeserializedLogLines() as $LogLine) {
            if(empty($displayableLogs[$LogLine->getRoundNumber()])){
                $displayableLogs[$LogLine->getRoundNumber()] = [];
            }
            array_push($displayableLogs[$LogLine->getRoundNumber()], $LogLine);
        }

        return $displayableLogs;
    }
    
    /* -------------------------------------------------------------------------- */
    /*                                  IN COMBAT                                 */
    /* -------------------------------------------------------------------------- */

    public $arena = null;

    /**
     * Creates a log for the winner announcement
     */
    public function addWinnerLog(int $winner)
    {
        $logLine = new LogLine(LogLine::TYPE_WINNER, $this->arena->currentRound);
        $logLine->initTypeWinner($winner);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for the use of an attack
     */
    public function addAttackLog(Fighter $fighter, Attack $attack, bool $isCrit): void
    {
        $logLine = new LogLine(LogLine::TYPE_ATTACK, $this->arena->currentRound);
        $logLine->initTypeAttack($fighter, $attack, $isCrit);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for dodging an attack
     */
    public function addDodgeLog(Fighter $fighter): void
    {
        $logLine = new LogLine(LogLine::TYPE_DODGE, $this->arena->currentRound);
        $logLine->initTypeDodge($fighter);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for receiving damage
     */
    public function addReceiveDamageLog(Fighter $fighter, int $physicalDamageTaken, int $specialDamageTaken): void
    {
        $logLine = new LogLine(LogLine::TYPE_DAMAGE_TAKEN_BY_ATTACK, $this->arena->currentRound);
        $logLine->initTypeDamageTakenByAttack($fighter, $physicalDamageTaken, $specialDamageTaken);

        $this->addLogLine($logLine);

        if($fighter->isKO()){
            $logLine = new LogLine(LogLine::TYPE_KO, $this->arena->currentRound);
            $logLine->initTypeKO($fighter);
    
            $this->addLogLine($logLine);
        }
        else {
            $logLine = new LogLine(LogLine::TYPE_REMAINING_VITALITY, $this->arena->currentRound);
            $logLine->initTypeRemainingVitality($fighter);
    
            $this->addLogLine($logLine);
        }    
    }

    /**
     * Creates a log for receiving damage from a Damaging Status
     */
    public function addReceiveDamageFromStatusLog(Fighter $fighter, $damageTaken, $status): void
    {
        $logLine = new LogLine(LogLine::TYPE_DAMAGE_TAKEN_BY_STATUS, $this->arena->currentRound);
        $logLine->initTypeDamageTakenByStatus($fighter, $damageTaken, $status);

        $this->addLogLine($logLine);

        if($fighter->isKO()){
            $logLine = new LogLine(LogLine::TYPE_KO, $this->arena->currentRound);
            $logLine->initTypeKO($fighter);
    
            $this->addLogLine($logLine);
        }
        else {
            $logLine = new LogLine(LogLine::TYPE_REMAINING_VITALITY, $this->arena->currentRound);
            $logLine->initTypeRemainingVitality($fighter);
    
            $this->addLogLine($logLine);
        }    
    }

    /**
     * Creates a log for receiving healing
     */
    public function addReceiveHealingLog(Fighter $fighter, int $healAmount): void
    {
        $logLine = new LogLine(LogLine::TYPE_HEALING_TAKEN_BY_ATTACK, $this->arena->currentRound);
        $logLine->initTypeHealingTakenByAttack($fighter, $healAmount);

        $this->addLogLine($logLine);

        $logLine = new LogLine(LogLine::TYPE_REMAINING_VITALITY, $this->arena->currentRound);
        $logLine->initTypeRemainingVitality($fighter);
    
        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for receiving a statisticModifier status
     */
    public function addStatisticModifierStatusReceivedLog(Fighter $fighter, StatisticModifierStatus $status): void
    {
        $logLine = new LogLine(LogLine::TYPE_STATISTIC_MODIFIER_RECEIVED, $this->arena->currentRound);
        $logLine->initTypeStatisticModifierStatusReceived($fighter, $status);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for loosing a statisticModifier status
     */
    public function addStatisticModifierStatusLossLog(Fighter $fighter, StatisticModifierStatus $status): void
    {
        $logLine = new LogLine(LogLine::TYPE_STATISTIC_MODIFIER_LOSS, $this->arena->currentRound);
        $logLine->initTypeStatisticModifierStatusLoss($fighter, $status);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for receiving a damaging status
     */
    public function addDamagingStatusReceivedLog(Fighter $fighter, DamagingStatus $status): void
    {   
        $logLine = new LogLine(LogLine::TYPE_DAMAGING_STATUS_RECEIVED, $this->arena->currentRound);
        $logLine->initTypeDamagingStatusReceived($fighter, $status);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for loosing a damaging status
     */
    public function addDamagingStatusLossLog(Fighter $fighter, DamagingStatus $status): void
    {   
        $logLine = new LogLine(LogLine::TYPE_DAMAGING_STATUS_LOSS, $this->arena->currentRound);
        $logLine->initTypeDamagingStatusLoss($fighter, $status);

        $this->addLogLine($logLine);
    }


    /**
     * Creates a log for receiving a control status
     */
    public function addControlStatusReceivedLog(Fighter $fighter, ControlStatus $status): void
    {   
        $logLine = new LogLine(LogLine::TYPE_CONTROL_STATUS_RECEIVED, $this->arena->currentRound);
        $logLine->initTypeControlStatusReceived($fighter, $status);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for loosing a control status
     */
    public function addControlStatusLossLog(Fighter $fighter, ControlStatus $status): void
    {
        $logLine = new LogLine(LogLine::TYPE_CONTROL_STATUS_LOSS, $this->arena->currentRound);
        $logLine->initTypeControlStatusLoss($fighter, $status);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for a control status activation
     */
    public function addControlStatusActivateLog(Fighter $fighter, string $statusType): void
    {
        $logLine = new LogLine(LogLine::TYPE_CONTROL_STATUS_ACTIVATE, $this->arena->currentRound);
        $logLine->initTypeControlStatusActivate($fighter, $statusType);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for a control status no effect
     */
    public function addControlStatusNoEffect(Fighter $fighter, string $statusType): void
    {
        $logLine = new LogLine(LogLine::TYPE_CONTROL_STATUS_NO_EFFECT, $this->arena->currentRound);
        $logLine->initTypeControlStatusNoEffect($fighter, $statusType);

        $this->addLogLine($logLine);
    }

    /**
     * Creates a log for a attack no effect
     */
    public function addAttackNoEffectLog(): void
    {
        $logLine = new LogLine(LogLine::TYPE_ATTACK_NO_EFFECT, $this->arena->currentRound);
        $logLine->initTypeAttackNoEffect();

        $this->addLogLine($logLine);
    }
}
