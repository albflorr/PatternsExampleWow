<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Factory;

use AzerothEngine\Creational\Singleton\GameWorld;
use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\Model\PlayerCharacter;

class HordeCharacterFactory extends CharacterFactory
{
    protected function validateRaceClassCombination(Race $race, CharacterClass $class): void
    {
        if ($race->getFaction() !== 'horde') {
            throw new \InvalidArgumentException("Race {$race->value} is not a Horde race");
        }
        $validCombinations = [
            Race::Orc->value => [CharacterClass::Warrior, CharacterClass::Hunter],
            Race::Undead->value => [CharacterClass::Warrior, CharacterClass::Mage],
        ];
        if (!in_array($class, $validCombinations[$race->value] ?? [])) {
            throw new \InvalidArgumentException("Class {$class->value} is not available for {$race->value}");
        }
    }

    protected function createCharacterInstance(string $id, string $name, Race $race, CharacterClass $class): PlayerCharacter
    {
        return new PlayerCharacter($id, $name, $class, $race);
    }

    protected function initializeCharacter(PlayerCharacter $character): void
    {
        $character->addGold(\AzerothEngine\Domain\ValueObject\GoldAmount::fromSilver(50));
        GameWorld::getInstance()->addPlayer($character);
    }
}