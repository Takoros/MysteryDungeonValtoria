<?php

namespace App\Formatter;

class CharacterFormatter
{
    public function formatCharacter($character){
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
            "age" => $character->getAge(),
            "description" => $character->getDescription(),
            "level" => $character->getLevel(),
            "xp" => $character->getXp(),
            "rank" => $character->getRank(),
            "statPoints" => $character->getStatPoints(),
            "discordUserId" => $character->getUserI()->getDiscordTag(),
            "stats" => $characterStatsArray
        ];
    }
}
