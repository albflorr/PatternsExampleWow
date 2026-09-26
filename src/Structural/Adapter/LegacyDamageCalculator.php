<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Adapter;

class LegacyDamageCalculator
{
    public function computePhysicalDamage(array $attackerStats, array $defenderStats): int
    {
        $baseDamage = $attackerStats['attack_power'] ?? 0;
        $armor = $defenderStats['armor'] ?? 0;
        $levelDiff = ($defenderStats['level'] ?? 1) - ($attackerStats['level'] ?? 1);

        $mitigation = min(0.75, $armor / ($armor + 400 + 85 * $levelDiff));
        $damage = $baseDamage * (1 - $mitigation);

        $variance = 0.9 + mt_rand() / mt_getrandmax() * 0.2;
        return (int) ($damage * $variance);
    }

    public function computeSpellDamage(array $attackerStats, array $defenderStats, string $school): int
    {
        $spellPower = $attackerStats['spell_power'] ?? 0;
        $resistance = $defenderStats["{$school}_resistance"] ?? 0;
        $levelDiff = ($defenderStats['level'] ?? 1) - ($attackerStats['level'] ?? 1);

        $mitigation = min(0.75, $resistance / ($resistance + 400 + 85 * $levelDiff));
        $damage = $spellPower * (1 - $mitigation);

        return (int) ($damage * (0.85 + mt_rand() / mt_getrandmax() * 0.3));
    }

    public function computeHealing(array $healerStats): int
    {
        $spellPower = $healerStats['spell_power'] ?? 0;
        return (int) ($spellPower * 1.5 * (0.9 + mt_rand() / mt_getrandmax() * 0.2));
    }
}