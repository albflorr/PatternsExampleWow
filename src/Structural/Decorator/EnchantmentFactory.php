<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Decorator;

class EnchantmentFactory
{
    public static function applyEnchantment(WeaponInterface $weapon, string $enchantmentType, array $params = []): WeaponInterface
    {
        return match ($enchantmentType) {
            'fire' => new FireEnchantment($weapon, $params['damage'] ?? 20, $params['chance'] ?? 0.15),
            'frost' => new FrostEnchantment($weapon, $params['damage'] ?? 15, $params['chance'] ?? 0.2, $params['slow'] ?? 0.5),
            'arcane' => new ArcaneEnchantment($weapon, $params['damage'] ?? 25, $params['mana_burn_chance'] ?? 0.1, $params['mana_burn'] ?? 30),
            'lifesteal' => new LifestealEnchantment($weapon, $params['percent'] ?? 0.1),
            'crusader' => new CrusaderEnchantment($weapon, $params['strength'] ?? 100, $params['chance'] ?? 0.05, $params['duration'] ?? 15),
            default => throw new \InvalidArgumentException("Unknown enchantment: {$enchantmentType}"),
        };
    }

    public static function applyMultiple(WeaponInterface $weapon, array $enchantments): WeaponInterface
    {
        $result = $weapon;
        foreach ($enchantments as $enchantment) {
            $result = self::applyEnchantment($result, $enchantment['type'], $enchantment['params'] ?? []);
        }
        return $result;
    }
}