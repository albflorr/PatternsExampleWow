<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Adapter;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;

interface DamageCalculatorInterface
{
    public function calculateDamage(PlayerCharacter $attacker, PlayerCharacter $defender, DamageType $type): int;
    public function calculateHealing(PlayerCharacter $healer, PlayerCharacter $target): int;
}