<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Inventory;

/** @var InventoryAggregate[] */
class InMemoryInventoryRepository implements InventoryRepositoryInterface
{
    private array $inventories = [];

    public function save(InventoryAggregate $inventory): void
    {
        $this->inventories[$inventory->getCharacterId()] = $inventory;
    }

    public function findByCharacterId(string $characterId): ?InventoryAggregate
    {
        return $this->inventories[$characterId] ?? null;
    }

    public function delete(string $characterId): void
    {
        unset($this->inventories[$characterId]);
    }
}