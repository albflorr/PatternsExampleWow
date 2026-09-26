<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

class DeadState extends BaseState
{
    private int $releaseTimer = 0;

    public function __construct()
    {
        parent::__construct(CharacterState::Dead);
    }

    public function enter(PlayerCharacter $character): void
    {
        parent::enter($character);
        $character->die();
        $this->releaseTimer = 30;
    }

    public function update(PlayerCharacter $character): void
    {
        $this->releaseTimer--;
        if ($this->releaseTimer <= 0) {
            echo "[{$character->getName()}] Spirit release available\n";
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

    public function onHeal(PlayerCharacter $character, int $amount): void
    {
        // Healing doesn't work while dead unless it's a resurrection
    }
}