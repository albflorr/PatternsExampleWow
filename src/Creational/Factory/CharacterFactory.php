<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Factory;

use AzerothEngine\Creational\Singleton\GameWorld;
use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\Model\PlayerCharacter;

interface CharacterFactoryInterface
{
    public function createCharacter(string $name, Race $race, CharacterClass $class): PlayerCharacter;
}

abstract class CharacterFactory implements CharacterFactoryInterface
{
    protected int $characterCounter = 0;

    public function createCharacter(string $name, Race $race, CharacterClass $class): PlayerCharacter
    {
        $this->validateRaceClassCombination($race, $class);
        $id = $this->generateCharacterId($race, $class);
        $character = $this->createCharacterInstance($id, $name, $race, $class);
        $this->initializeCharacter($character);
        return $character;
    }

    abstract protected function validateRaceClassCombination(Race $race, CharacterClass $class): void;
    abstract protected function createCharacterInstance(string $id, string $name, Race $race, CharacterClass $class): PlayerCharacter;
    abstract protected function initializeCharacter(PlayerCharacter $character): void;

    protected function generateCharacterId(Race $race, CharacterClass $class): string
    {
        $this->characterCounter++;
        return strtolower("{$race->value}_{$class->value}_{$this->characterCounter}");
    }
}

