<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Repository;

use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemSlot;

interface ItemRepositoryInterface
{
    public function save(Item $item): void;
    public function findById(ItemId $id): ?Item;
    public function findBySlot(ItemSlot $slot): array;
    public function findByLevelRange(int $minLevel, int $maxLevel): array;
    public function findAll(): array;
}