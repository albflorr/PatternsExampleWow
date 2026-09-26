<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

class FrostEnchantment extends WeaponDecorator
{
    private int $frostDamage;
    private float $slowChance;
    private float $slowPercent;

    public function __construct(WeaponInterface $weapon, int $frostDamage = 15, float $slowChance = 0.2, float $slowPercent = 0.5)
    {
        parent::__construct($weapon);
        $this->frostDamage = $frostDamage;
        $this->slowChance = $slowChance;
        $this->slowPercent = $slowPercent;
    }

    public function getName(): string
    {
        return $this->weapon->getName() . ' of Frost';
    }

    public function getDamageType(): DamageType
    {
        return DamageType::Frost;
    }

    public function getAttributes(): Attributes
    {
        return $this->weapon->getAttributes()->add(new Attributes(frostResistance: -$this->frostDamage));
    }

    public function calculateDPS(PlayerCharacter $character): float
    {
        $baseDPS = $this->weapon->calculateDPS($character);
        $frostDPS = $this->frostDamage * $this->slowChance * (1 / $this->getAttackSpeed());
        return $baseDPS + $frostDPS;
    }

    public function getFrostDamage(): int
    {
        return $this->frostDamage;
    }

    public function getSlowChance(): float
    {
        return $this->slowChance;
    }
}