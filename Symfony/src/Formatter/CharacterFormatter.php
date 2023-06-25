<?php

namespace App\Formatter;

use App\Entity\Attack;
use App\Entity\Character;
use App\Entity\Rotation;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class CharacterFormatter
{
    public function formatCharacter(Character $character){
        $characterStats = $character->getStats();
        $characterStatsArray = [
            "vitality" => $characterStats->getVitality(),
            "strength" => $characterStats->getStrength(),
            "stamina" => $characterStats->getStamina(),
            "power" => $characterStats->getPower(),
            "bravery" => $characterStats->getBravery(),
            "presence" => $characterStats->getPresence(),
            "impassiveness" => $characterStats->getImpassiveness(),
            "agility" => $characterStats->getAgility(),
            "coordination" => $characterStats->getCoordination(),
            "speed" => $characterStats->getSpeed(),
            "actionPoint" => $characterStats->getActionPoint()
        ];

        return [
            "id" => $character->getId(),
            "name" => $character->getName(),
            "gender" => $character->getGender(),
            "species" => $character->getSpecies()->getName(),
            "age" => $character->getAge(),
            "description" => $character->getDescription(),
            "level" => $character->getLevel(),
            "xp" => $character->getXp(),
            "nextLevelXP" => $character->getXPCeil(),
            "rank" => $character->getRank(),
            "statPoints" => $character->getStatPoints(),
            "discordUserId" => $character->getUserI()->getDiscordTag(),
            "stats" => $characterStatsArray
        ];
    }

    public function formatRotation(Character $character, string $rotationType): array
    {
        if($rotationType === Rotation::TYPE_OPENER){
            $rotation = $character->getOpenerRotation();
        }
        else if($rotationType === Rotation::TYPE_ROTATION){
            $rotation = $character->getRotation();
        }
        else {
            throw new InvalidParameterException();
        }

        return [
            'id' => $rotation->getId(),
            'type' => $rotation->getType(),
            'attackOne' => $this->formatAttack($rotation->getAttackOne()),
            'attackTwo' => $this->formatAttack($rotation->getAttackTwo()),
            'attackThree' => $this->formatAttack($rotation->getAttackThree()),
            'attackFour' => $this->formatAttack($rotation->getAttackFour()),
            'attackFive' => $this->formatAttack($rotation->getAttackFive()),
            'characterId' => $character->getId()
        ];
    }

    public function formatAttack(Attack $attack): array
    {
        return [
            'id' => $attack->getId(),
            'name' => $attack->getName(),
            'description' => $attack->getDescription(),
            'power' => $attack->getPower(),
            'statusPower' => $attack->getStatusPower(),
            'criticalPower' => $attack->getCriticalPower(),
            'actionPointCost' => $attack->getActionPointCost(),
            'scope' => $attack->getScope(),
            'type' => $attack->getType()->getName()
        ];
    }
}
