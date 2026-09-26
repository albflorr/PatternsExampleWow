<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

class StunnedState extends BaseState
{
    private int $remainingDuration;

    public function __construct(int $duration = 3)
    {
        parent::__construct(CharacterState::Stunned);
        $this->remainingDuration = $duration;
    }

    public function enter(PlayerCharacter $character): void
    {
        parent::enter($character);
        $this->remainingDuration = 3;
    }

    public function update(PlayerCharacter $character): void
    {
        $this->remainingDuration--;
        if ($this->remainingDuration <= 0) {
            $character->setState(CharacterState::Normal);
        }
    }

    public function canAttack(): bool
    {
        return false;
    }

    public function canMove(): bool
    {
        return false;
    }

    public function canCast(): bool
    {
        return false;
    }
}