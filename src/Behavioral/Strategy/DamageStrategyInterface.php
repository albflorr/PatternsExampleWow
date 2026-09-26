<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;

interface DamageStrategyInterface
{
    public function calculate(PlayerCharacter $attacker, PlayerCharacter $defender, int $baseDamage, DamageType $type): int;
    public function getName(): string;
}