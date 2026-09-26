<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Factory;

use AzerothEngine\Domain\ValueObject\ArmorType;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemLevel;
use AzerothEngine\Domain\ValueObject\ItemQuality;
use AzerothEngine\Domain\ValueObject\ItemSlot;
use AzerothEngine\Domain\ValueObject\WeaponType;

class AllianceEquipmentFactory extends EquipmentFactory
{
    public function __construct()
    {
        parent::__construct('alliance');
        $this->prefixes = ['Light', 'Holy', 'Silver', 'Royal', 'Stormwind', 'Ironforge', 'Darnassus'];
        $this->materials = ['Mithral', 'Truesilver', 'Thorium', 'Lightsteel', 'Blessed', 'Runed'];
    }

    public function createHelmet(int $level): Item
    {
        return $this->createArmor('Helmet', ItemSlot::Head, $level, ArmorType::Plate, new Attributes(intellect: $level * 2, spirit: $level, stamina: $level));
    }

    public function createChest(int $level): Item
    {
        return $this->createArmor('Chestplate', ItemSlot::Chest, $level, ArmorType::Plate, new Attributes(intellect: $level * 3, spirit: $level * 2, stamina: $level * 2, armor: $level * 5));
    }

    public function createLegs(int $level): Item
    {
        return $this->createArmor('Legplates', ItemSlot::Legs, $level, ArmorType::Plate, new Attributes(intellect: $level * 2, spirit: $level, stamina: $level, armor: $level * 3));
    }

    public function createBoots(int $level): Item
    {
        return $this->createArmor('Greaves', ItemSlot::Feet, $level, ArmorType::Plate, new Attributes(intellect: $level, spirit: $level, stamina: $level, armor: $level * 2));
    }

    public function createGloves(int $level): Item
    {
        return $this->createArmor('Gauntlets', ItemSlot::Hands, $level, ArmorType::Plate, new Attributes(intellect: $level, spirit: $level, stamina: $level, armor: $level));
    }

    public function createMainHand(int $level): Item
    {
        return $this->createWeapon('Sword', ItemSlot::MainHand, $level, WeaponType::Sword, new Attributes(intellect: $level * 3, spirit: $level * 2));
    }

    public function createOffHand(int $level): Item
    {
        return $this->createWeapon('Shield', ItemSlot::OffHand, $level, WeaponType::Shield, new Attributes(intellect: $level, spirit: $level, armor: $level * 10));
    }
}