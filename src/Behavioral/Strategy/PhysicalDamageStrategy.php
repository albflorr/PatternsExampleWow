<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;

class PhysicalDamageStrategy implements DamageStrategyInterface
{
    public function calculate(PlayerCharacter $attacker, PlayerCharacter $defender, int $baseDamage, DamageType $type): int
    {
        $attackPower = $attacker->getAttackPower();
        $defenderArmor = $defender->getTotalAttributes()->armor;
        $levelDiff = $defender->getLevel() - $attacker->getLevel();

        $armorMitigation = $this->calculateArmorMitigation($defenderArmor, $defender->getLevel(), $levelDiff);
        $damage = ($baseDamage + $attackPower) * (1 - $armorMitigation);

        $critMultiplier = $this->calculateCrit($attacker);
        $variance = 0.95 + mt_rand() / mt_getrandmax() * 0.1;

        return (int) ($damage * $critMultiplier * $variance);
    }

    public function getName(): string
    {
        return 'Physical';
    }

    private function calculateArmorMitigation(int $armor, int $defenderLevel, int $levelDiff): float
    {
        $effectiveArmor = max(0, $armor - $levelDiff * 100);
        $constant = $defenderLevel * 5 + 400;
        return min(0.75, $effectiveArmor / ($effectiveArmor + $constant));
    }

    private function calculateCrit(PlayerCharacter $character): float
    {
        $critChance = $character->getCritChance() / 100;
        return (mt_rand() / mt_getrandmax() < $critChance) ? 2.0 : 1.0;
    }
}