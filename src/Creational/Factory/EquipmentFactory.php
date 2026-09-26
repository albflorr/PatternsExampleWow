<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Factory;

use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\ValueObject\ArmorType;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemLevel;
use AzerothEngine\Domain\ValueObject\ItemQuality;
use AzerothEngine\Domain\ValueObject\ItemSlot;
use AzerothEngine\Domain\ValueObject\WeaponType;

interface EquipmentFactoryInterface
{
    public function createHelmet(int $level): Item;
    public function createChest(int $level): Item;
    public function createLegs(int $level): Item;
    public function createBoots(int $level): Item;
    public function createGloves(int $level): Item;
    public function createMainHand(int $level): Item;
    public function createOffHand(int $level): Item;
}

abstract class EquipmentFactory implements EquipmentFactoryInterface
{
    protected string $faction;
    protected array $prefixes = [];
    protected array $materials = [];

    public function __construct(string $faction)
    {
        $this->faction = $faction;
    }

    protected function createArmor(
        string $name,
        ItemSlot $slot,
        int $level,
        ArmorType $armorType,
        Attributes $attributes
    ): Item {
        $quality = $this->determineQuality($level);
        $prefix = $this->prefixes[array_rand($this->prefixes)];
        $material = $this->materials[array_rand($this->materials)];
        $fullName = "{$prefix} {$material} {$name}";

        return new Item(
            id: new ItemId(strtolower("{$this->faction}_{$slot->value}_{$level}")),
            name: $fullName,
            slot: $slot,
            quality: $quality,
            level: new ItemLevel($level),
            attributes: $attributes,
            buyPrice: $this->calculateBuyPrice($level, $quality),
            sellPrice: $this->calculateSellPrice($level, $quality)
        );
    }

    protected function createWeapon(
        string $name,
        ItemSlot $slot,
        int $level,
        WeaponType $weaponType,
        Attributes $attributes
    ): Item {
        $quality = $this->determineQuality($level);
        $prefix = $this->prefixes[array_rand($this->prefixes)];
        $material = $this->materials[array_rand($this->materials)];
        $fullName = "{$prefix} {$material} {$name}";

        return new Item(
            id: new ItemId(strtolower("{$this->faction}_{$slot->value}_{$level}")),
            name: $fullName,
            slot: $slot,
            quality: $quality,
            level: new ItemLevel($level),
            attributes: $attributes,
            buyPrice: $this->calculateBuyPrice($level, $quality)->multiply(2),
            sellPrice: $this->calculateSellPrice($level, $quality)->multiply(2)
        );
    }

    private function determineQuality(int $level): ItemQuality
    {
        return match (true) {
            $level >= 50 => ItemQuality::Epic,
            $level >= 40 => ItemQuality::Rare,
            $level >= 30 => ItemQuality::Uncommon,
            $level >= 10 => ItemQuality::Common,
            default => ItemQuality::Poor,
        };
    }

    private function calculateBuyPrice(int $level, ItemQuality $quality): GoldAmount
    {
        $basePrice = $level * 100;
        $qualityMultiplier = match ($quality) {
            ItemQuality::Poor => 0.5,
            ItemQuality::Common => 1.0,
            ItemQuality::Uncommon => 2.0,
            ItemQuality::Rare => 5.0,
            ItemQuality::Epic => 20.0,
            ItemQuality::Legendary => 100.0,
        };
        return GoldAmount::fromCopper((int) ($basePrice * $qualityMultiplier));
    }

    private function calculateSellPrice(int $level, ItemQuality $quality): GoldAmount
    {
        $buyPrice = $this->calculateBuyPrice($level, $quality);
        return GoldAmount::fromCopper((int) ($buyPrice->copper * 0.25));
    }

    abstract public function createHelmet(int $level): Item;
    abstract public function createChest(int $level): Item;
    abstract public function createLegs(int $level): Item;
    abstract public function createBoots(int $level): Item;
    abstract public function createGloves(int $level): Item;
    abstract public function createMainHand(int $level): Item;
    abstract public function createOffHand(int $level): Item;
}

