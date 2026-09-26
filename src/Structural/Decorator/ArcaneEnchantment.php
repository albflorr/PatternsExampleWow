<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

class ArcaneEnchantment extends WeaponDecorator
{
    private int $arcaneDamage;
    private float $manaBurnChance;
    private int $manaBurnAmount;

    public function __construct(WeaponInterface $weapon, int $arcaneDamage = 25, float $manaBurnChance = 0.1, int $manaBurnAmount = 30)
    {
        parent::__construct($weapon);
        $this->arcaneDamage = $arcaneDamage;
        $this->manaBurnChance = $manaBurnChance;
        $this->manaBurnAmount = $manaBurnAmount;
    }

    public function getName(): string
    {
        return $this->weapon->getName() . ' of Arcane';
    }

    public function getDamageType(): DamageType
    {
        return DamageType::Arcane;
    }

    public function getAttributes(): Attributes
    {
        return $this->weapon->getAttributes()->add(new Attributes(arcaneResistance: -$this->arcaneDamage));
    }

    public function calculateDPS(PlayerCharacter $character): float
    {
        $baseDPS = $this->weapon->calculateDPS($character);
        $arcaneDPS = $this->arcaneDamage * $this->manaBurnChance * (1 / $this->getAttackSpeed());
        return $baseDPS + $arcaneDPS;
    }
}