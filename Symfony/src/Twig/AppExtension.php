<?php

namespace App\Twig;

use App\Service\Combat\Status\StatusInterface;
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
            new TwigFilter('damaging', [$this, 'displayDamaging'])
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
}