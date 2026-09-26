<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Prototype;

use AzerothEngine\Domain\ValueObject\HealthPoints;

class MonsterPrototypeManager
{
    /** @var MonsterPrototype[] */
    private array $prototypes = [];

    public function __construct()
    {
        $this->registerPrototype(new BeastPrototype());
        $this->registerPrototype(new HumanoidPrototype());
        $this->registerPrototype(new UndeadPrototype());
        $this->registerPrototype(new DragonkinPrototype());
    }

    public function registerPrototype(MonsterPrototype $prototype): void
    {
        $this->prototypes[$prototype->getId()] = $prototype;
    }

    public function spawnMonster(string $prototypeId, array $overrides = []): MonsterPrototype
    {
        if (!isset($this->prototypes[$prototypeId])) {
            throw new \InvalidArgumentException("Prototype not found: {$prototypeId}");
        }

        $monster = $this->prototypes[$prototypeId]->clone();

        if (isset($overrides['level'])) {
            $monster->setLevel($overrides['level']);
            $monster->setHealth(HealthPoints::full($monster->getHealth()->maximum + ($overrides['level'] - 1) * 50));
        }
        if (isset($overrides['name'])) {
            $monster->setName($overrides['name']);
        }
        if (isset($overrides['damage_multiplier'])) {
            $monster->setBaseDamage((int) ($monster->getBaseDamage() * $overrides['damage_multiplier']));
        }

        return $monster;
    }

    public function spawnHorde(string $prototypeId, int $count, array $overrides = []): array
    {
        $horde = [];
        for ($i = 0; $i < $count; $i++) {
            $horde[] = $this->spawnMonster($prototypeId, $overrides);
        }
        return $horde;
    }

    public function getPrototype(string $prototypeId): ?MonsterPrototype
    {
        return $this->prototypes[$prototypeId] ?? null;
    }

    public function getAvailablePrototypes(): array
    {
        return array_keys($this->prototypes);
    }
}