<?php

namespace App\Service\Items;

class Weapon {
    public const WEAPON_TYPE_SWORD = 'weapon_type_sword';
    public const WEAPON_TYPE_STAFF = 'weapon_type_staff';
    public const WEAPON_TYPE_GAUNTELETS = 'weapon_type_gauntelets';
    public const WEAPON_TYPE_DAGGERS = 'weapon_type_daggers';

    public float $weaponPower;
    public string $type;

    /** Stats **/
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