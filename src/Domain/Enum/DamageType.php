<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Enum;

enum DamageType: string
{
    case Physical = 'physical';
    case Fire = 'fire';
    case Frost = 'frost';
    case Arcane = 'arcane';
    case Shadow = 'shadow';
    case Nature = 'nature';
    case Holy = 'holy';

    public function getResistanceAttribute(): string
    {
        return match ($this) {
            self::Physical => 'armor',
            self::Fire => 'fire_resistance',
            self::Frost => 'frost_resistance',
            self::Arcane => 'arcane_resistance',
            self::Shadow => 'shadow_resistance',
            self::Nature => 'nature_resistance',
            self::Holy => 'holy_resistance',
        };
    }
}