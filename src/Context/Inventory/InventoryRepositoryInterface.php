<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Inventory;

use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemSlot;

interface InventoryRepositoryInterface
{
    public function save(InventoryAggregate $inventory): void;
    public function findByCharacterId(string $characterId): ?InventoryAggregate;
    public function delete(string $characterId): void;
}