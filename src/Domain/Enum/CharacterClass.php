<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Enum;

enum CharacterClass: string
{
    case Warrior = 'warrior';
    case Mage = 'mage';
    case Hunter = 'hunter';

    public function getPrimaryAttribute(): string
    {
        return match ($this) {
            self::Warrior => 'strength',
            self::Mage => 'intellect',
            self::Hunter => 'agility',
        };
    }

    public function getBaseHealth(): int
    {
        return match ($this) {
            self::Warrior => 150,
            self::Mage => 80,
            self::Hunter => 100,
        };
    }

    public function getBaseMana(): int
    {
        return match ($this) {
            self::Warrior => 0,
            self::Mage => 200,
            self::Hunter => 50,
        };
    }
}