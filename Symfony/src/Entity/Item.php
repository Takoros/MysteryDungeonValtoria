<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use App\Service\Items\Accessory;
use App\Service\Items\Scarf;
use App\Service\Items\Weapon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

enum ItemTypeEnum:string {
    case ITEM_TYPE_WEAPON = 'item_type_weapon';
    case ITEM_TYPE_SCARF = 'item_type_scarf';
    case ITEM_TYPE_ACCESSORY = 'item_type_accessory';
}

enum ItemRarityEnum:string {
    case ITEM_RARITY_NONE = 'item_rarity_none';
    case ITEM_RARITY_COMMON = 'item_rarity_common';
    case ITEM_RARITY_RARE = 'item_rarity_rare';
    case ITEM_RARITY_EPIC = 'item_rarity_epic';
    case ITEM_RARITY_LEGENDARY = 'item_rarity_legendary';
}

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?ItemTypeEnum $type = null;

    #[ORM\Column]
    private array $properties = [];

    #[ORM\Column(length: 25)]
    private ?ItemRarityEnum $rarity = null;

    #[ORM\Column]
    private ?int $levelRequired = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\ManyToOne(inversedBy: 'Items')]
    private ?Inventory $inventory = null;

    #[ORM\OneToOne(mappedBy: 'Weapon', cascade: ['persist', 'remove'])]
    private ?Gear $equipedAsWeaponGear = null;

    #[ORM\OneToOne(mappedBy: 'Scarf', cascade: ['persist', 'remove'])]
    private ?Gear $equipedAsScarfGear = null;

    #[ORM\OneToOne(mappedBy: 'Accessory', cascade: ['persist', 'remove'])]
    private ?Gear $equipedAsAccessoryGear = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?ItemTypeEnum
    {
        return $this->type;
    }

    public function setType(ItemTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getRarity(): ?ItemRarityEnum
    {
        return $this->rarity;
    }

    public function setRarity(ItemRarityEnum $rarity): self
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getLevelRequired(): ?int
    {
        return $this->levelRequired;
    }

    public function setLevelRequired(int $levelRequired): self
    {
        $this->levelRequired = $levelRequired;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                                   MAKERS                                   */
    /* -------------------------------------------------------------------------- */

    public function makeWeapon(string $name, ItemRarityEnum $rarity, string $weaponType, float $weaponPower, int $value, int $levelRequired, array $stats): void
    {
        $this->setName($name)
             ->setRarity($rarity)
             ->setLevelRequired($levelRequired)
             ->setValue($value)
             ->setType(ItemTypeEnum::ITEM_TYPE_WEAPON);

        $properties = new Weapon();
        $properties->weaponPower = $weaponPower;
        $properties->type = $weaponType;

        foreach ($stats as $statName => $value) {
            $properties->$statName = $value;
        }

        $this->setProperties($properties->expose());
    }

    public function makeScarf(string $name, ItemRarityEnum $rarity, int $value, int $levelRequired, array $stats): void
    {
        $this->setName($name)
             ->setRarity($rarity)
             ->setLevelRequired($levelRequired)
             ->setValue($value)
             ->setType(ItemTypeEnum::ITEM_TYPE_SCARF);

        $properties = new Scarf();
        
        foreach ($stats as $statName => $value) {
            $properties->$statName = $value;
        }

        $this->setProperties($properties->expose());
    }

    public function makeAccessory(string $name,ItemRarityEnum $rarity, int $value, int $levelRequired, array $stats): void
    {
        $this->setName($name)
             ->setRarity($rarity)
             ->setLevelRequired($levelRequired)
             ->setValue($value)
             ->setType(ItemTypeEnum::ITEM_TYPE_ACCESSORY);

        $properties = new Accessory();
        
        foreach ($stats as $statName => $value) {
            $properties->$statName = $value;
        }

        $this->setProperties($properties->expose());
    }
}
