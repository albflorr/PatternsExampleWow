<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

readonly class Item
{
    public function __construct(
        public ItemId $id,
        public string $name,
        public ItemSlot $slot,
        public ItemQuality $quality,
        public ItemLevel $level,
        public Attributes $attributes,
        public GoldAmount $buyPrice,
        public GoldAmount $sellPrice,
        public bool $isEquippable = true
    ) {}
}