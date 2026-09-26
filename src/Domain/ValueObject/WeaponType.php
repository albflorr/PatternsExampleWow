<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

enum WeaponType: string
{
    case Sword = 'sword';
    case Axe = 'axe';
    case Mace = 'mace';
    case Dagger = 'dagger';
    case Staff = 'staff';
    case Bow = 'bow';
    case Gun = 'gun';
    case Crossbow = 'crossbow';
    case Wand = 'wand';
    case Fist = 'fist';
    case Polearm = 'polearm';
    case Shield = 'shield';
}