<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

class CrusaderEnchantment extends WeaponDecorator
{
    private int $strengthBonus;
    private float $procChance;
    private int $duration;

    public function __construct(WeaponInterface $weapon, int $strengthBonus = 100, float $procChance = 0.05, int $duration = 15)
    {
        parent::__construct($weapon);
        $this->strengthBonus = $strengthBonus;
        $this->procChance = $procChance;
        $this->duration = $duration;
    }

    public function getName(): string
    {
        return $this->weapon->getName() . ' of the Crusader';
    }

    public function getAttributes(): Attributes
    {
        return $this->weapon->getAttributes()->add(new Attributes(strength: $this->strengthBonus));
    }

    public function calculateDPS(PlayerCharacter $character): float
    {
        $baseDPS = $this->weapon->calculateDPS($character);
        $avgStrengthBonus = $this->strengthBonus * $this->procChance * ($this->duration / 60);
        $bonusDPS = $avgStrengthBonus * 2 / $this->getAttackSpeed();
        return $baseDPS + $bonusDPS;
    }
}