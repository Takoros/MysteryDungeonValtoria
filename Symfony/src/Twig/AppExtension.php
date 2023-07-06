<?php

namespace App\Twig;

use App\Entity\DungeonInstance;
use App\Service\Combat\Status\StatusInterface;
use App\Service\Dungeon\DungeonGenerationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('statistic', [$this, 'displayStatistic']),
            new TwigFilter('control', [$this, 'displayControl']),
            new TwigFilter('controlActivate', [$this, 'displayControlActivate']),
            new TwigFilter('damaging', [$this, 'displayDamaging']),
            new TwigFilter('dungeonTile', [$this, 'giveCssClassDungeonTile']),
            new TwigFilter('speciesIcon', [$this, 'getSpeciesIconFileName']),
            new TwigFilter('DungeonInstanceStatus', [$this, 'getDungeonInstanceStatusName'])
        ];
    }

    public function displayStatistic(string $statistic): string
    {
        if($statistic === 'vitality'){
            return 'sa Vitalité';
        }

        if($statistic === 'strength'){
            return 'sa Force';
        }

        if($statistic === 'stamina'){
            return 'son Endurance';
        }

        if($statistic === 'power'){
            return 'son Pouvoir';
        }

        if($statistic === 'bravery'){
            return 'son Courage';
        }

        if($statistic === 'presence'){
            return 'sa Présence';
        }

        if($statistic === 'impassiveness'){
            return 'son Impassibilité';
        }

        if($statistic === 'agility'){
            return 'son Agilité';
        }

        if($statistic === 'coordination'){
            return 'sa Coordination';
        }

        if($statistic === 'speed'){
            return 'sa Vitesse';
        }
    }

    public function displayControl(string $control): string
    {
        if($control === StatusInterface::TYPE_CONTROL_CONFUSION){
            return 'Confusion';
        }

        if($control === StatusInterface::TYPE_CONTROL_FATIGUE){
            return 'Fatigue';
        }

        if($control === StatusInterface::TYPE_CONTROL_FREEZE){
            return 'Gel';
        }

        if($control === StatusInterface::TYPE_CONTROL_PARALYSIS){
            return 'Paralysie';
        }

        if($control === StatusInterface::TYPE_CONTROL_PETRIFICATION){
            return 'Pétrification';
        }

        if($control === StatusInterface::TYPE_CONTROL_SLEEP){
            return 'Sommeil';
        }

        if($control === StatusInterface::TYPE_CONTROL_YAWN){
            return 'Baillement';
        }
    }

    public function displayControlActivate(string $control, string $fighterName): string
    {
        if($control === StatusInterface::TYPE_CONTROL_CONFUSION){
            return $fighterName . " est confus, il/elle s'attaque.";
        }

        if($control === StatusInterface::TYPE_CONTROL_FATIGUE){
            return $fighterName . " est fatigué, il/elle se repose.";
        }

        if($control === StatusInterface::TYPE_CONTROL_FREEZE){
            return $fighterName . " est gelé, il/elle ne peut pas bouger.";
        }

        if($control === StatusInterface::TYPE_CONTROL_PARALYSIS){
            return $fighterName . " est paralysé, il/elle ne peut pas bouger.";
        }

        if($control === StatusInterface::TYPE_CONTROL_PETRIFICATION){
            return $fighterName . " est pétrifié, il/elle ne peut pas bouger.";
        }

        if($control === StatusInterface::TYPE_CONTROL_SLEEP){
            return $fighterName . " est endormi, il/elle ne peut pas bouger.";
        }

        if($control === StatusInterface::TYPE_CONTROL_YAWN){
            return $fighterName . " baille, il/elle va s'endormir sous peu.";
        }
    }

    public function displayDamaging(string $damaging)
    {
        if($damaging === StatusInterface::TYPE_DAMAGING_BURN){
            return 'brûlure';
        }

        if($damaging === StatusInterface::TYPE_DAMAGING_POISON){
            return 'empoisonnement';
        }

        if($damaging === StatusInterface::TYPE_DAMAGING_BAD_POISON){
            return 'empoisonnement grave';
        }
    }

    public function giveCssClassDungeonTile($data, $currentExplorersPosition, $tilePosition): string
    {
        $data = (array) $data;
        $classList = 'tile';

        if($tilePosition === $currentExplorersPosition){
            $classList .= ' tile-current-explorers-position';
        }

        if ($data['box'] === DungeonGenerationService::DUNGEON_BOX_EMPTY){
            $classList .= ' tile-empty';
        }
        else if($data['isExplorated'] === DungeonGenerationService::DUNGEON_TILE_UNKNOWN){
            $classList .= ' tile-unknown';
        }
        else if($data['box'] === DungeonGenerationService::DUNGEON_BOX_ENTRANCE){
            $classList .= ' tile-entrance';
        }
        else if ($data['box'] === DungeonGenerationService::DUNGEON_BOX_EXIT){
            $classList .= ' tile-exit';
        }
        else if (array_key_exists('monsters', $data) && $data['monsters'] !== null){
            $classList .= ' tile-monsters-' . count($data['monsters']);
        }
        else if($data['box'] === DungeonGenerationService::DUNGEON_BOX_FULL){
            $classList .= ' tile-full';
        }

        return $classList;
    }

    public function getSpeciesIconFileName($speciesName)
    {
        $speciesName = strtolower($speciesName);
        $speciesName = str_replace("É","e", $speciesName);
        $speciesName = str_replace("é","e", $speciesName);
        $speciesName = str_replace("È","e", $speciesName);
        $speciesName = str_replace("è","e", $speciesName);
        $speciesName = str_replace("Â","a", $speciesName);
        $speciesName = str_replace("â","a", $speciesName);

        return 'pokemon-icons/'. $speciesName .'.png';
    }

    public function getDungeonInstanceStatusName($dungeonInstanceStatus): string
    {
        if($dungeonInstanceStatus === DungeonInstance::DUNGEON_STATUS_PREPARATION){
            return 'Préparation';
        }
        else if($dungeonInstanceStatus === DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return 'Exploration';
        }
        else if($dungeonInstanceStatus === DungeonInstance::DUNGEON_STATUS_TERMINATION){
            return 'Terminaison';
        }
    }
}