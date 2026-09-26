<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Inventory;

use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemSlot;

class InventoryService
{
    private InventoryRepositoryInterface $repository;

    public function __construct(InventoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getInventory(string $characterId): InventoryAggregate
    {
        $inventory = $this->repository->findByCharacterId($characterId);
        if ($inventory === null) {
            $inventory = new InventoryAggregate($characterId);
            $this->repository->save($inventory);
        }
        return $inventory;
    }

    public function equipItem(string $characterId, Item $item): bool
    {
        $inventory = $this->getInventory($characterId);
        $result = $inventory->equipItem($item);
        $this->repository->save($inventory);
        return $result;
    }

    public function unequipItem(string $characterId, ItemSlot $slot): ?Item
    {
        $inventory = $this->getInventory($characterId);
        $item = $inventory->unequipItem($slot);
        $this->repository->save($inventory);
        return $item;
    }

    public function addItemToBag(string $characterId, Item $item): bool
    {
        $inventory = $this->getInventory($characterId);
        $result = $inventory->addToBag($item);
        $this->repository->save($inventory);
        return $result;
    }

    public function removeItemFromBag(string $characterId, ItemId $itemId): ?Item
    {
        $inventory = $this->getInventory($characterId);
        $item = $inventory->removeFromBag($itemId);
        $this->repository->save($inventory);
        return $item;
    }

    public function expandBag(string $characterId, int $slots): void
    {
        $inventory = $this->getInventory($characterId);
        $inventory->expandBag($slots);
        $this->repository->save($inventory);
    }
}