<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

class PoisonedState extends BaseState
{
    private int $tickDamage;
    private int $remainingDuration;
    private int $tickInterval = 3;

    public function __construct(int $duration = 15, int $tickDamage = 10)
    {
        parent::__construct(CharacterState::Poisoned);
        $this->remainingDuration = $duration;
        $this->tickDamage = $tickDamage;
    }

    public function enter(PlayerCharacter $character): void
    {
        parent::enter($character);
        $this->remainingDuration = 15;
    }

    public function update(PlayerCharacter $character): void
    {
        $this->remainingDuration--;
        $this->tickInterval--;

        if ($this->tickInterval <= 0) {
            $character->takeDamage($this->tickDamage);
            echo "[{$character->getName()}] Poison tick: {$this->tickDamage} damage\n";
            $this->tickInterval = 3;
        }

        if ($this->remainingDuration <= 0) {
            $character->setState(CharacterState::Normal);
        }
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

    public function onHeal(PlayerCharacter $character, int $amount): void
    {
        // Poison reduces healing effectiveness
        $reducedHeal = (int) ($amount * 0.5);
        $character->heal($reducedHeal);
        echo "[{$character->getName()}] Healing reduced by poison: {$reducedHeal} instead of {$amount}\n";
    }
}