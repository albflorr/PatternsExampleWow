<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

abstract class WeaponDecorator implements WeaponInterface
{
    protected WeaponInterface $weapon;

    public function __construct(WeaponInterface $weapon)
    {
        $this->weapon = $weapon;
    }

    public function getName(): string
    {
        return $this->weapon->getName();
    }

    public function getBaseDamage(): int
    {
        return $this->weapon->getBaseDamage();
    }

    public function getDamageType(): DamageType
    {
        return $this->weapon->getDamageType();
    }

    public function getAttributes(): Attributes
    {
        return $this->weapon->getAttributes();
    }

    public function getAttackSpeed(): float
    {
        return $this->weapon->getAttackSpeed();
    }

    public function calculateDPS(PlayerCharacter $character): float
    {
        return $this->weapon->calculateDPS($character);
    }
}