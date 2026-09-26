<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;

class SpellDamageStrategy implements DamageStrategyInterface
{
    public function calculate(PlayerCharacter $attacker, PlayerCharacter $defender, int $baseDamage, DamageType $type): int
    {
        $spellPower = $attacker->getSpellPower();
        $resistance = $defender->getTotalAttributes()->{$type->getResistanceAttribute()} ?? 0;
        $levelDiff = $defender->getLevel() - $attacker->getLevel();

        $resistanceMitigation = $this->calculateResistanceMitigation($resistance, $defender->getLevel(), $levelDiff);
        $damage = ($baseDamage + $spellPower) * (1 - $resistanceMitigation);

        $critMultiplier = $this->calculateSpellCrit($attacker);
        $variance = 0.9 + mt_rand() / mt_getrandmax() * 0.2;

        return (int) ($damage * $critMultiplier * $variance);
    }

    public function getName(): string
    {
        return 'Spell';
    }

    private function calculateResistanceMitigation(int $resistance, int $defenderLevel, int $levelDiff): float
    {
        $effectiveResistance = max(0, $resistance - $levelDiff * 5);
        $constant = $defenderLevel * 5 + 100;
        return min(0.75, $effectiveResistance / ($effectiveResistance + $constant));
    }

    private function calculateSpellCrit(PlayerCharacter $character): float
    {
        $critChance = ($character->getTotalAttributes()->intellect * 0.02) / 100;
        return (mt_rand() / mt_getrandmax() < $critChance) ? 1.5 : 1.0;
    }
}