<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

class CharacterStateContext
{
    private PlayerCharacter $character;
    private StateInterface $currentState;

    public function __construct(PlayerCharacter $character)
    {
        $this->character = $character;
        $this->currentState = new NormalState();
        $this->currentState->enter($character);
    }

    public function transitionTo(CharacterState $newState, array $params = []): void
    {
        $newStateInstance = $this->createState($newState, $params);

        if ($newStateInstance === null) {
            return;
        }

        if ($this->currentState->getState() === $newState) {
            return;
        }

        $this->currentState->exit($this->character);
        $this->currentState = $newStateInstance;
        $this->character->setState($newState);
        $this->currentState->enter($this->character);
    }

    private function createState(CharacterState $state, array $params): ?StateInterface
    {
        return match ($state) {
            CharacterState::Normal => new NormalState(),
            CharacterState::Stunned => new StunnedState($params['duration'] ?? 3),
            CharacterState::Dead => new DeadState(),
            CharacterState::Poisoned => new PoisonedState($params['duration'] ?? 15, $params['tick_damage'] ?? 10),
            CharacterState::Frozen => new FrozenState($params['duration'] ?? 5),
        };
    }

    public function tick(): void
    {
        $this->currentState->update($this->character);
    }

    public function onDamageTaken(int $amount): void
    {
        $this->currentState->onDamageTaken($this->character, $amount);
    }

    public function onHeal(int $amount): void
    {
        $this->currentState->onHeal($this->character, $amount);
    }

    public function canAttack(): bool
    {
        return $this->currentState->canAttack();
    }

    public function canMove(): bool
    {
        return $this->currentState->canMove();
    }

    public function canCast(): bool
    {
        return $this->currentState->canCast();
    }

    public function getCurrentState(): CharacterState
    {
        return $this->currentState->getState();
    }

    public function __toString(): string
    {
        return "{$this->character->getName()} is {$this->currentState->getState()->value}";
    }
}