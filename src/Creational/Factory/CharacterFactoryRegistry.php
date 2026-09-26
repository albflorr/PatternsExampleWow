<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Factory;

use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\Model\PlayerCharacter;

class CharacterFactoryRegistry
{
    /** @var CharacterFactoryInterface[] */
    private static array $factories = [];

    public static function register(string $faction, CharacterFactoryInterface $factory): void
    {
        self::$factories[$faction] = $factory;
    }

    public static function getFactory(string $faction): ?CharacterFactoryInterface
    {
        return self::$factories[$faction] ?? null;
    }

    public static function createCharacter(string $faction, string $name, Race $race, CharacterClass $class): PlayerCharacter
    {
        $factory = self::getFactory($faction);
        if ($factory === null) {
            throw new \InvalidArgumentException("No factory registered for faction: {$faction}");
        }
        return $factory->createCharacter($name, $race, $class);
    }
}