<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Inventory;

use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemSlot;

class InventoryAggregate
{
    private string $characterId;
    /** @var array<ItemSlot, Item|null> */
    private array $equippedItems = [];
    /** @var Item[] */
    private array $bagItems = [];
    private int $bagCapacity = 16;
    private int $version = 0;

    public function __construct(string $characterId)
    {
        $this->characterId = $characterId;
    }

    public function getCharacterId(): string
    {
        return $this->characterId;
    }

    public function getEquippedItems(): array
    {
        return $this->equippedItems;
    }

    public function getBagItems(): array
    {
        return $this->bagItems;
    }

    public function getBagCapacity(): int
    {
        return $this->bagCapacity;
    }

    public function getBagUsedSlots(): int
    {
        return count($this->bagItems);
    }

    public function getBagFreeSlots(): int
    {
        return $this->bagCapacity - count($this->bagItems);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function equipItem(Item $item): bool
    {
        if (!$item->isEquippable) {
            return false;
        }

        if (isset($this->equippedItems[$item->slot->value])) {
            $this->unequipItem($item->slot);
        }

        $this->equippedItems[$item->slot->value] = $item;
        $this->version++;
        return true;
    }

    public function unequipItem(ItemSlot $slot): ?Item
    {
        $item = $this->equippedItems[$slot->value] ?? null;
        if ($item !== null) {
            unset($this->equippedItems[$slot->value]);
            $this->addToBag($item);
            $this->version++;
        }
        return $item;
    }

    public function addToBag(Item $item): bool
    {
        if (count($this->bagItems) >= $this->bagCapacity) {
            return false;
        }
        $this->bagItems[] = $item;
        $this->version++;
        return true;
    }

    public function removeFromBag(ItemId $itemId): ?Item
    {
        foreach ($this->bagItems as $index => $item) {
            if ($item->id->value === $itemId->value) {
                unset($this->bagItems[$index]);
                $this->bagItems = array_values($this->bagItems);
                $this->version++;
                return $item;
            }
        }
        return null;
    }

    public function moveToBag(ItemSlot $slot): bool
    {
        $item = $this->equippedItems[$slot->value] ?? null;
        if ($item === null) {
            return false;
        }
        return $this->addToBag($item);
    }

    public function expandBag(int $additionalSlots): void
    {
        $this->bagCapacity += $additionalSlots;
        $this->version++;
    }

    public function getTotalWeight(): int
    {
        $weight = 0;
        foreach ($this->bagItems as $item) {
            $weight += $item->level->value;
        }
        foreach ($this->equippedItems as $item) {
            if ($item !== null) {
                $weight += $item->level->value;
            }
        }
        return $weight;
    }
}