<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Factory;

class EquipmentFactoryRegistry
{
    /** @var EquipmentFactoryInterface[] */
    private static array $factories = [];

    public static function register(string $faction, EquipmentFactoryInterface $factory): void
    {
        self::$factories[$faction] = $factory;
    }

    public static function getFactory(string $faction): ?EquipmentFactoryInterface
    {
        return self::$factories[$faction] ?? null;
    }

    public static function createFullSet(string $faction, int $level): array
    {
        $factory = self::getFactory($faction);
        if ($factory === null) {
            throw new \InvalidArgumentException("No equipment factory for faction: {$faction}");
        }

        return [
            'head' => $factory->createHelmet($level),
            'chest' => $factory->createChest($level),
            'legs' => $factory->createLegs($level),
            'feet' => $factory->createBoots($level),
            'hands' => $factory->createGloves($level),
            'main_hand' => $factory->createMainHand($level),
            'off_hand' => $factory->createOffHand($level),
        ];
    }
}