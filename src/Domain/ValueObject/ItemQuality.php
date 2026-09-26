<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

enum ItemQuality: string
{
    case Poor = 'poor';
    case Common = 'common';
    case Uncommon = 'uncommon';
    case Rare = 'rare';
    case Epic = 'epic';
    case Legendary = 'legendary';

    public function getColor(): string
    {
        return match ($this) {
            self::Poor => '#9d9d9d',
            self::Common => '#ffffff',
            self::Uncommon => '#1eff00',
            self::Rare => '#0070dd',
            self::Epic => '#a335ee',
            self::Legendary => '#ff8000',
        };
    }
}