<?php

namespace App\Service\Items;

class Accessory {
    public int $vitality = 0;
    public int $strength = 0;
    public int $stamina = 0;
    public int $power = 0;
    public int $bravery = 0;
    public int $presence = 0;
    public int $impassiveness = 0;
    public int $agility = 0;
    public int $coordination = 0;
    public int $speed = 0;
    public int $actionPoint = 0;

    public function expose() {
        return get_object_vars($this);
    }
}