<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Repository;

use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemSlot;

/** @var Item[] */
class InMemoryItemRepository implements ItemRepositoryInterface
{
    private array $items = [];

    public function save(Item $item): void
    {
        $this->items[$item->id->value] = $item;
    }

    public function findById(ItemId $id): ?Item
    {
        return $this->items[$id->value] ?? null;
    }

    public function findBySlot(ItemSlot $slot): array
    {
        return array_filter($this->items, fn($item) => $item->slot === $slot);
    }

    public function findByLevelRange(int $minLevel, int $maxLevel): array
    {
        return array_filter($this->items, fn($item) => $item->level->value >= $minLevel && $item->level->value <= $maxLevel);
    }

    public function findAll(): array
    {
        return array_values($this->items);
    }
}