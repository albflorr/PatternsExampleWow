<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

abstract class BaseState implements StateInterface
{
    protected CharacterState $state;

    public function __construct(CharacterState $state)
    {
        $this->state = $state;
    }

    public function enter(PlayerCharacter $character): void
    {
        echo "[{$character->getName()}] Entered state: {$this->state->value}\n";
    }

    public function exit(PlayerCharacter $character): void
    {
        echo "[{$character->getName()}] Exited state: {$this->state->value}\n";
    }

    public function update(PlayerCharacter $character): void
    {
    }

    public function onDamageTaken(PlayerCharacter $character, int $amount): void
    {
    }

    public function onHeal(PlayerCharacter $character, int $amount): void
    {
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

    public function getState(): CharacterState
    {
        return $this->state;
    }
}