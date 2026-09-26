<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

enum ItemSlot: string
{
    case Head = 'head';
    case Neck = 'neck';
    case Shoulder = 'shoulder';
    case Back = 'back';
    case Chest = 'chest';
    case Wrist = 'wrist';
    case Hands = 'hands';
    case Waist = 'waist';
    case Legs = 'legs';
    case Feet = 'feet';
    case Finger1 = 'finger1';
    case Finger2 = 'finger2';
    case Trinket1 = 'trinket1';
    case Trinket2 = 'trinket2';
    case MainHand = 'main_hand';
    case OffHand = 'off_hand';
    case Ranged = 'ranged';
}