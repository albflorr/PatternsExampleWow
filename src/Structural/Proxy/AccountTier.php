<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Proxy;

enum AccountTier: string
{
    case Free = 'free';
    case Premium = 'premium';
    case Veteran = 'veteran';
    case GM = 'gm';
}