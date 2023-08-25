<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $inventorySize = null;
    
    #[ORM\Column]
    private ?int $oracee = null;
    
    #[ORM\OneToMany(mappedBy: 'inventory', targetEntity: Item::class)]
    private Collection $Items;

    public function __construct()
    {
        $this->Items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventorySize(): ?int
    {
        return $this->inventorySize;
    }

    public function setInventorySize(int $inventorySize): self
    {
        $this->inventorySize = $inventorySize;

        return $this;
    }

    public function getOracee(): ?int
    {
        return $this->oracee;
    }

    public function setOracee(int $oracee): self
    {
        $this->oracee = $oracee;

        return $this;
    }

    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->Items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->Items->contains($item)) {
            $this->Items->add($item);
            $item->setInventory($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->Items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getInventory() === $this) {
                $item->setInventory(null);
            }
        }

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                             INVENTORY CREATION                             */
    /* -------------------------------------------------------------------------- */

    public function initNewInventory(): void
    {
        $this->setOracee(10)
             ->setInventorySize(8);
    }
}
