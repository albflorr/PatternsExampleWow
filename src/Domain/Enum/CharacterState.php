<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Enum;

enum CharacterState: string
{
    case Normal = 'normal';
    case Stunned = 'stunned';
    case Dead = 'dead';
    case Poisoned = 'poisoned';
    case Frozen = 'frozen';

    public function canAct(): bool
    {
        return match ($this) {
            self::Normal => true,
            self::Stunned, self::Frozen, self::Dead => false,
            self::Poisoned => true,
        };
    }

    public function canMove(): bool
    {
        return match ($this) {
            self::Normal, self::Poisoned => true,
            self::Stunned, self::Frozen, self::Dead => false,
        };
    }
}