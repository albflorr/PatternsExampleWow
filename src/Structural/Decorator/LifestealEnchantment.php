<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Model\PlayerCharacter;

class LifestealEnchantment extends WeaponDecorator
{
    private float $lifestealPercent;

    public function __construct(WeaponInterface $weapon, float $lifestealPercent = 0.1)
    {
        parent::__construct($weapon);
        $this->lifestealPercent = $lifestealPercent;
    }

    public function getName(): string
    {
        return $this->weapon->getName() . ' of the Leech';
    }

    public function calculateDPS(PlayerCharacter $character): float
    {
        return $this->weapon->calculateDPS($character);
    }

    public function getHealAmount(int $damageDealt): int
    {
        return (int) ($damageDealt * $this->lifestealPercent);
    }
}