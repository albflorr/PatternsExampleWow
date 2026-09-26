<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Adapter;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

class ModernDamageCalculator implements DamageCalculatorInterface
{
    public function calculateDamage(PlayerCharacter $attacker, PlayerCharacter $defender, DamageType $type): int
    {
        $attackerAttrs = $attacker->getTotalAttributes();
        $defenderAttrs = $defender->getTotalAttributes();
        $levelDiff = $defender->getLevel() - $attacker->getLevel();

        $baseDamage = match ($type) {
            DamageType::Physical => $attacker->getAttackPower(),
            default => $attacker->getSpellPower(),
        };

        $resistance = $defenderAttrs->{$type->getResistanceAttribute()} ?? 0;
        $mitigation = $this->calculateMitigation($resistance, $defender->getLevel(), $levelDiff);

        $damage = $baseDamage * (1 - $mitigation);
        $critMultiplier = $this->calculateCritMultiplier($attacker);
        $variance = 0.95 + mt_rand() / mt_getrandmax() * 0.1;

        return (int) ($damage * $critMultiplier * $variance);
    }

    public function calculateHealing(PlayerCharacter $healer, PlayerCharacter $target): int
    {
        $spellPower = $healer->getSpellPower();
        $critMultiplier = $this->calculateCritMultiplier($healer);
        $variance = 0.95 + mt_rand() / mt_getrandmax() * 0.1;

        return (int) ($spellPower * 1.8 * $critMultiplier * $variance);
    }

    private function calculateMitigation(int $resistance, int $defenderLevel, int $levelDiff): float
    {
        $effectiveResistance = max(0, $resistance - $levelDiff * 5);
        $constant = $defenderLevel * 5 + 100;
        return min(0.75, $effectiveResistance / ($effectiveResistance + $constant));
    }

    private function calculateCritMultiplier(PlayerCharacter $character): float
    {
        $critChance = $character->getCritChance() / 100;
        if (mt_rand() / mt_getrandmax() < $critChance) {
            return 2.0;
        }
        return 1.0;
    }
}