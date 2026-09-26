<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

class FireEnchantment extends WeaponDecorator
{
    private int $fireDamage;
    private float $procChance;

    public function __construct(WeaponInterface $weapon, int $fireDamage = 20, float $procChance = 0.15)
    {
        parent::__construct($weapon);
        $this->fireDamage = $fireDamage;
        $this->procChance = $procChance;
    }

    public function getName(): string
    {
        return $this->weapon->getName() . ' of Fire';
    }

    public function getDamageType(): DamageType
    {
        return DamageType::Fire;
    }

    public function getAttributes(): Attributes
    {
        return $this->weapon->getAttributes()->add(new Attributes(fireResistance: -$this->fireDamage));
    }

    public function calculateDPS(PlayerCharacter $character): float
    {
        $baseDPS = $this->weapon->calculateDPS($character);
        $fireDPS = $this->fireDamage * $this->procChance * (1 / $this->getAttackSpeed());
        return $baseDPS + $fireDPS;
    }

    public function getFireDamage(): int
    {
        return $this->fireDamage;
    }

    public function getProcChance(): float
    {
        return $this->procChance;
    }
}