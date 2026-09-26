<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Enum;

enum Race: string
{
    case Orc = 'orc';
    case Human = 'human';
    case NightElf = 'night_elf';
    case Undead = 'undead';

    public function getFaction(): string
    {
        return match ($this) {
            self::Orc, self::Undead => 'horde',
            self::Human, self::NightElf => 'alliance',
        };
    }

    public function getRacialBonus(): array
    {
        return match ($this) {
            self::Orc => ['strength' => 3, 'stamina' => 2],
            self::Human => ['intellect' => 2, 'spirit' => 3],
            self::NightElf => ['agility' => 3, 'intellect' => 1],
            self::Undead => ['stamina' => 2, 'spirit' => 2],
        };
    }
}