<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\Item;

class BaseWeapon implements WeaponInterface
{
    protected Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function getName(): string
    {
        return $this->item->name;
    }

    public function getBaseDamage(): int
    {
        return $this->item->attributes->strength + $this->item->attributes->agility;
    }

    public function getDamageType(): DamageType
    {
        return DamageType::Physical;
    }

    public function getAttributes(): Attributes
    {
        return $this->item->attributes;
    }

    public function getAttackSpeed(): float
    {
        return 2.0;
    }

    public function calculateDPS(PlayerCharacter $character): float
    {
        $damage = $this->getBaseDamage() + $character->getAttackPower();
        return $damage / $this->getAttackSpeed();
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}