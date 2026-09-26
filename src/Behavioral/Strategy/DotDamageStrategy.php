<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;

class DotDamageStrategy implements DamageStrategyInterface
{
    public function calculate(PlayerCharacter $attacker, PlayerCharacter $defender, int $baseDamage, DamageType $type): int
    {
        $spellPower = $attacker->getSpellPower();
        $tickDamage = ($baseDamage + $spellPower * 0.1) / 5;

        $resistance = $defender->getTotalAttributes()->{$type->getResistanceAttribute()} ?? 0;
        $levelDiff = $defender->getLevel() - $attacker->getLevel();

        $resistanceMitigation = $this->calculateResistanceMitigation($resistance, $defender->getLevel(), $levelDiff);
        $damage = $tickDamage * (1 - $resistanceMitigation);

        return (int) $damage;
    }

    public function getName(): string
    {
        return 'DoT';
    }

    private function calculateResistanceMitigation(int $resistance, int $defenderLevel, int $levelDiff): float
    {
        $effectiveResistance = max(0, $resistance - $levelDiff * 5);
        $constant = $defenderLevel * 5 + 100;
        return min(0.75, $effectiveResistance / ($effectiveResistance + $constant));
    }
}