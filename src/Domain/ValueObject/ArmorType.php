<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

enum ArmorType: string
{
    case Cloth = 'cloth';
    case Leather = 'leather';
    case Mail = 'mail';
    case Plate = 'plate';
    case Shield = 'shield';
}