<?php

namespace App\Entity;

use App\Repository\GearRepository;
use App\Service\Items\Weapon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GearRepository::class)]
class Gear
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'equipedAsWeaponGear', cascade: ['persist', 'remove'])]
    private ?Item $Weapon = null;

    #[ORM\OneToOne(inversedBy: 'equipedAsScarfGear', cascade: ['persist', 'remove'])]
    private ?Item $Scarf = null;

    #[ORM\OneToOne(inversedBy: 'equipedAsAccessoryGear', cascade: ['persist', 'remove'])]
    private ?Item $Accessory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeapon(): ?Item
    {
        return $this->Weapon;
    }

    public function getScarf(): ?Item
    {
        return $this->Scarf;
    }

    public function getAccessory(): ?Item
    {
        return $this->Accessory;
    }

    public function equip(Item $item): self
    {
        match($item->getType()){
            ItemTypeEnum::ITEM_TYPE_WEAPON => $this->Weapon = $item,
            ItemTypeEnum::ITEM_TYPE_SCARF => $this->Scarf = $item,
            ItemTypeEnum::ITEM_TYPE_ACCESSORY => $this->Accessory = $item,
        };

        return $this;
    }

    /**
     * Returns the bonuses gained on a stat by wearing the current gear
     */
    public function getStatBonuses(string $statName): int
    {
        $bonus = 0;

        if($this->Weapon !== null){
            $bonus += $this->Weapon->getProperties()[$statName];
        }

        if($this->Scarf !== null){
            $bonus += $this->Scarf->getProperties()[$statName];
        }

        if($this->Accessory !== null){
            $bonus += $this->Accessory->getProperties()[$statName];
        }

        return $bonus;
    }

    /* -------------------------------------------------------------------------- */
    /*                                GEAR CREATION                               */
    /* -------------------------------------------------------------------------- */

    public function initNewGear(EntityManagerInterface $em): void
    {
        $weapon = new Item();
        $weapon->makeWeapon('weapon_epee_en_bois', ItemRarityEnum::ITEM_RARITY_COMMON, Weapon::WEAPON_TYPE_SWORD, 2, 1, 1, []);

        $scarf = new Item();
        $scarf->makeScarf('scarf_foulard_du_debutant', ItemRarityEnum::ITEM_RARITY_COMMON, 1, 1, []);

        $accessory = new Item();
        $accessory->makeAccessory('accessory_badge_explorateur_debutant', ItemRarityEnum::ITEM_RARITY_COMMON, 1, 1, []);

        $em->persist($weapon);
        $em->persist($scarf);
        $em->persist($accessory);

        $this->equip($weapon);
        $this->equip($scarf);
        $this->equip($accessory);
    }
}
