<?php

namespace App\Twig;

use App\Entity\DungeonInstance;
use App\Entity\RaidInstance;
use App\Entity\Type;
use App\Service\Combat\Status\StatusInterface;
use App\Service\Dungeon\DungeonGenerationService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('statistic', [$this, 'displayStatistic']),
            new TwigFilter('control', [$this, 'displayControl']),
            new TwigFilter('controlActivate', [$this, 'displayControlActivate']),
            new TwigFilter('damaging', [$this, 'displayDamaging']),
            new TwigFilter('dungeonTile', [$this, 'giveCssClassDungeonTile']),
            new TwigFilter('speciesIcon', [$this, 'getSpeciesIconFileName']),
            new TwigFilter('DungeonInstanceStatus', [$this, 'getInstanceStatusName']),
            new TwigFilter('RaidInstanceStatus', [$this, 'getInstanceStatusName']),
            new TwigFilter('typeIcon', [$this, 'getTypeIconName']),
            new TwigFilter('transAttack', [$this, 'translateAttackName']),
            new TwigFilter('transAttackDesc', [$this, 'translateAttackDesc']),
            new TwigFilter('transSpecies', [$this, 'translateSpeciesName'])
        ];
    }

    public function displayStatistic(string $statistic): string
    {
        if($statistic === 'vitality'){
            return $this->translator->trans('log_display_stat_vitality', [], 'app');
        }

        if($statistic === 'strength'){
            return $this->translator->trans('log_display_stat_strength', [], 'app');
        }

        if($statistic === 'stamina'){
            return $this->translator->trans('log_display_stat_stamina', [], 'app');
        }

        if($statistic === 'power'){
            return $this->translator->trans('log_display_stat_power', [], 'app');
        }

        if($statistic === 'bravery'){
            return $this->translator->trans('log_display_stat_bravery', [], 'app');
        }

        if($statistic === 'presence'){
            return $this->translator->trans('log_display_stat_presence', [], 'app');
        }

        if($statistic === 'impassiveness'){
            return $this->translator->trans('log_display_stat_impassiveness', [], 'app');
        }

        if($statistic === 'agility'){
            return $this->translator->trans('log_display_stat_agility', [], 'app');
        }

        if($statistic === 'coordination'){
            return $this->translator->trans('log_display_stat_coordination', [], 'app');
        }

        if($statistic === 'speed'){
            return $this->translator->trans('log_display_stat_speed', [], 'app');
        }
    }

    public function displayControl(string $control): string
    {
        if($control === StatusInterface::TYPE_CONTROL_CONFUSION){
            return $this->translator->trans('log_display_control_status_confusion', [], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_FATIGUE){
            return $this->translator->trans('log_display_control_status_fatigue', [], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_FREEZE){
            return $this->translator->trans('log_display_control_status_freeze', [], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_PARALYSIS){
            return $this->translator->trans('log_display_control_status_paralysis', [], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_PETRIFICATION){
            return $this->translator->trans('log_display_control_status_petrification', [], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_SLEEP){
            return $this->translator->trans('log_display_control_status_sleep', [], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_YAWN){
            return $this->translator->trans('log_display_control_status_yawn', [], 'app');
        }
    }

    public function displayControlActivate(string $control, string $fighterName): string
    {
        if($control === StatusInterface::TYPE_CONTROL_CONFUSION){
            return $this->translator->trans('log_control_status_activate_confusion', [
                '%fighterName%' => $fighterName
            ], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_FATIGUE){
            return $this->translator->trans('log_control_status_activate_fatigue', [
                '%fighterName%' => $fighterName
            ], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_FREEZE){
            return $this->translator->trans('log_control_status_activate_freeze', [
                '%fighterName%' => $fighterName
            ], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_PARALYSIS){
            return $this->translator->trans('log_control_status_activate_paralysis', [
                '%fighterName%' => $fighterName
            ], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_PETRIFICATION){
            return $this->translator->trans('log_control_status_activate_petrification', [
                '%fighterName%' => $fighterName
            ], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_SLEEP){
            return $this->translator->trans('log_control_status_activate_sleep', [
                '%fighterName%' => $fighterName
            ], 'app');
        }

        if($control === StatusInterface::TYPE_CONTROL_YAWN){
            return $this->translator->trans('log_control_status_activate_yawn', [
                '%fighterName%' => $fighterName
            ], 'app');
        }

    }

    public function displayDamaging(string $damaging)
    {
        if($damaging === StatusInterface::TYPE_DAMAGING_BURN){
            return $this->translator->trans('log_display_damaging_status_burn', [], 'app');
        }

        if($damaging === StatusInterface::TYPE_DAMAGING_POISON){
            return $this->translator->trans('log_display_damaging_status_poison', [], 'app');
        }

        if($damaging === StatusInterface::TYPE_DAMAGING_BAD_POISON){
            return $this->translator->trans('log_display_damaging_status_bad_poison', [], 'app');
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

    public function getSpeciesIconFileName($character)
    {
        if(gettype($character) === 'array'){
            $speciesName = $character['Species']['name'];
        }
        else {
            $speciesName = $character->getSpecies()->getName();
        }

        $speciesName = strtolower($speciesName);
        $speciesName = str_replace("É","e", $speciesName);
        $speciesName = str_replace("é","e", $speciesName);
        $speciesName = str_replace("È","e", $speciesName);
        $speciesName = str_replace("è","e", $speciesName);
        $speciesName = str_replace("Â","a", $speciesName);
        $speciesName = str_replace("â","a", $speciesName);

        if(gettype($character) === 'array'){
            if(array_key_exists('isShiny', $character)){
                if($character['isShiny'] === true){
                    $speciesName .= '_shiny';
                }
            }
        }
        else {
            if(method_exists($character::class, 'isShiny') &&  $character->isShiny()){
                $speciesName .= '_shiny';
            }
        }

        return 'pokemon-icons/'. $speciesName .'.png';
    }

    public function getTypeIconName(Type $type){
        $typeName = $type->getName();

        $typeName = strtolower($typeName);
        $typeName = str_replace("É","e", $typeName);
        $typeName = str_replace("é","e", $typeName);
        $typeName = str_replace("È","e", $typeName);
        $typeName = str_replace("è","e", $typeName);
        $typeName = str_replace("Â","a", $typeName);
        $typeName = str_replace("â","a", $typeName);

        return 'icons/pokemon-types/'. $typeName . '.png';
    }

    public function getInstanceStatusName($instanceStatus): string
    {
        /**
         * Dungeons
         */
        if($instanceStatus === DungeonInstance::DUNGEON_STATUS_PREPARATION){
            return 'Préparation';
        }
        else if($instanceStatus === DungeonInstance::DUNGEON_STATUS_EXPLORATION){
            return 'Exploration';
        }
        else if($instanceStatus === DungeonInstance::DUNGEON_STATUS_TERMINATION){
            return 'Fin';
        }
        
        /**
         * Raids
         */
        if($instanceStatus === RaidInstance::RAID_STATUS_PREPARATION){
            return 'Préparation';
        }
        else if($instanceStatus === RaidInstance::RAID_STATUS_EXPLORATION){
            return 'Exploration';
        }
        else if($instanceStatus === RaidInstance::RAID_STATUS_TERMINATION || $instanceStatus === RaidInstance::RAID_STATUS_TERMINATION_DEFEAT){
            return 'Fin';
        }
    }

    public function translateAttackName($attackName){
        $attackName = strtolower($attackName);
        
        $attackName = str_replace(' ', '_', $attackName);
        $attackName = str_replace('ô', 'o', $attackName);
        $attackName = str_replace('â', 'a', $attackName);
        $attackName = str_replace('à', 'a', $attackName);
        $attackName = str_replace('û', 'u', $attackName);
        $attackName = str_replace('é', 'e', $attackName);
        $attackName = str_replace('É', 'e', $attackName);
        $attackName = str_replace('è', 'e', $attackName);
        $attackName = str_replace('ç', 'c', $attackName);

        return $this->translator->trans($attackName.'_attack', [], 'app');
    }
    
    public function translateAttackDesc($attackName){
        $attackName = strtolower($attackName);
        
        $attackName = str_replace(' ', '_', $attackName);
        $attackName = str_replace('ô', 'o', $attackName);
        $attackName = str_replace('â', 'a', $attackName);
        $attackName = str_replace('à', 'a', $attackName);
        $attackName = str_replace('û', 'u', $attackName);
        $attackName = str_replace('é', 'e', $attackName);
        $attackName = str_replace('É', 'e', $attackName);
        $attackName = str_replace('è', 'e', $attackName);
        $attackName = str_replace('ç', 'c', $attackName);

        return $this->translator->trans($attackName.'_attack_description', [], 'app');
    }

    public function translateSpeciesName($species){
        return $this->translator->trans($species->getId().'_species_name', [], 'app');
    }
}