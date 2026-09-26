<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\Attributes;

interface WeaponInterface
{
    public function getName(): string;
    public function getBaseDamage(): int;
    public function getDamageType(): DamageType;
    public function getAttributes(): Attributes;
    public function getAttackSpeed(): float;
    public function calculateDPS(PlayerCharacter $character): float;
}