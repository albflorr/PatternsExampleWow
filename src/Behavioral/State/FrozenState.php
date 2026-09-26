<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

class FrozenState extends BaseState
{
    private int $remainingDuration;

    public function __construct(int $duration = 5)
    {
        parent::__construct(CharacterState::Frozen);
        $this->remainingDuration = $duration;
    }

    public function enter(PlayerCharacter $character): void
    {
        parent::enter($character);
        $this->remainingDuration = 5;
    }

    public function update(PlayerCharacter $character): void
    {
        $this->remainingDuration--;
        if ($this->remainingDuration <= 0) {
            $character->setState(CharacterState::Normal);
        }
    }

    public function onDamageTaken(PlayerCharacter $character, int $amount): void
    {
        // Frozen characters take 50% more damage
        $extraDamage = (int) ($amount * 0.5);
        $character->takeDamage($extraDamage);
        echo "[{$character->getName()}] Frozen - took {$extraDamage} extra damage!\n";
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