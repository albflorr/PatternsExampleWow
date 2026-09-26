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

class HordeEquipmentFactory extends EquipmentFactory
{
    public function __construct()
    {
        parent::__construct('horde');
        $this->prefixes = ['Blood', 'Doom', 'Shadow', 'Fel', 'Warsong', 'Blackrock', 'Thunder'];
        $this->materials = ['Orcish', 'Dark Iron', 'Fel Iron', 'Obsidian', 'Bone', 'Scourge'];
    }

    public function createHelmet(int $level): Item
    {
        return $this->createArmor('Helmet', ItemSlot::Head, $level, ArmorType::Plate, new Attributes(strength: $level * 2, stamina: $level));
    }

    public function createChest(int $level): Item
    {
        return $this->createArmor('Chestplate', ItemSlot::Chest, $level, ArmorType::Plate, new Attributes(strength: $level * 3, stamina: $level * 2, armor: $level * 5));
    }

    public function createLegs(int $level): Item
    {
        return $this->createArmor('Legplates', ItemSlot::Legs, $level, ArmorType::Plate, new Attributes(strength: $level * 2, stamina: $level, armor: $level * 3));
    }

    public function createBoots(int $level): Item
    {
        return $this->createArmor('Greaves', ItemSlot::Feet, $level, ArmorType::Plate, new Attributes(strength: $level, stamina: $level, armor: $level * 2));
    }

    public function createGloves(int $level): Item
    {
        return $this->createArmor('Gauntlets', ItemSlot::Hands, $level, ArmorType::Plate, new Attributes(strength: $level, stamina: $level, armor: $level));
    }

    public function createMainHand(int $level): Item
    {
        return $this->createWeapon('Axe', ItemSlot::MainHand, $level, WeaponType::Axe, new Attributes(strength: $level * 3, agility: $level));
    }

    public function createOffHand(int $level): Item
    {
        return $this->createWeapon('Axe', ItemSlot::OffHand, $level, WeaponType::Axe, new Attributes(strength: $level * 2, agility: $level));
    }
}