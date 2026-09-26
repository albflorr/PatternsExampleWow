<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Adapter;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

class LegacyDamageCalculatorAdapter implements DamageCalculatorInterface
{
    private LegacyDamageCalculator $legacyCalculator;

    public function __construct(?LegacyDamageCalculator $legacyCalculator = null)
    {
        $this->legacyCalculator = $legacyCalculator ?? new LegacyDamageCalculator();
    }

    public function calculateDamage(PlayerCharacter $attacker, PlayerCharacter $defender, DamageType $type): int
    {
        $attackerStats = $this->extractStats($attacker);
        $defenderStats = $this->extractStats($defender);

        return match ($type) {
            DamageType::Physical => $this->legacyCalculator->computePhysicalDamage($attackerStats, $defenderStats),
            default => $this->legacyCalculator->computeSpellDamage($attackerStats, $defenderStats, $type->value),
        };
    }

    public function calculateHealing(PlayerCharacter $healer, PlayerCharacter $target): int
    {
        $healerStats = $this->extractStats($healer);
        return $this->legacyCalculator->computeHealing($healerStats);
    }

    private function extractStats(PlayerCharacter $character): array
    {
        $attrs = $character->getTotalAttributes();
        return [
            'level' => $character->getLevel(),
            'attack_power' => $character->getAttackPower(),
            'spell_power' => $character->getSpellPower(),
            'armor' => $attrs->armor,
            'fire_resistance' => $attrs->fireResistance,
            'frost_resistance' => $attrs->frostResistance,
            'arcane_resistance' => $attrs->arcaneResistance,
            'shadow_resistance' => $attrs->shadowResistance,
            'nature_resistance' => $attrs->natureResistance,
            'holy_resistance' => $attrs->holyResistance,
        ];
    }
}