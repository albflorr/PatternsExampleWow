<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\State;

use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Model\PlayerCharacter;

interface StateInterface
{
    public function enter(PlayerCharacter $character): void;
    public function exit(PlayerCharacter $character): void;
    public function update(PlayerCharacter $character): void;
    public function onDamageTaken(PlayerCharacter $character, int $amount): void;
    public function onHeal(PlayerCharacter $character, int $amount): void;
    public function canAttack(): bool;
    public function canMove(): bool;
    public function canCast(): bool;
    public function getState(): CharacterState;
}