<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

class NormalState extends BaseState
{
    public function __construct()
    {
        parent::__construct(CharacterState::Normal);
    }

    public function canAttack(): bool
    {
        return true;
    }

    public function canMove(): bool
    {
        return true;
    }

    public function canCast(): bool
    {
        return true;
    }

    public function onDamageTaken(PlayerCharacter $character, int $amount): void
    {
        if ($character->getHealth()->getPercentage() < 20) {
            echo "[{$character->getName()}] Low health warning!\n";
        }
    }
}