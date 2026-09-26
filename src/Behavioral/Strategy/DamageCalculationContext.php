<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;

class DamageCalculationContext
{
    private DamageStrategyInterface $strategy;

    public function __construct(?DamageStrategyInterface $defaultStrategy = null)
    {
        $this->strategy = $defaultStrategy ?? new PhysicalDamageStrategy();
    }

    public function setStrategy(DamageStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function calculateDamage(PlayerCharacter $attacker, PlayerCharacter $defender, DamageType $type, int $baseDamage): int
    {
        $strategy = $this->selectStrategy($type);
        return $strategy->calculate($attacker, $defender, $baseDamage, $type);
    }

    private function selectStrategy(DamageType $type): DamageStrategyInterface
    {
        return match ($type) {
            DamageType::Physical => new PhysicalDamageStrategy(),
            DamageType::Fire, DamageType::Frost, DamageType::Arcane, DamageType::Shadow, DamageType::Nature, DamageType::Holy => new SpellDamageStrategy(),
        };
    }

    public function getCurrentStrategyName(): string
    {
        return $this->strategy->getName();
    }
}