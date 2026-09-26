<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Prototype;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\HealthPoints;
use AzerothEngine\Domain\ValueObject\ManaPoints;

class HumanoidPrototype extends MonsterPrototype
{
    public function __construct()
    {
        parent::__construct(
            id: 'humanoid_prototype',
            name: 'Bandit',
            level: 10,
            race: Race::Human,
            health: HealthPoints::full(500),
            mana: ManaPoints::full(100),
            attributes: new Attributes(strength: 20, agility: 18, intellect: 10, stamina: 15),
            experienceReward: 300,
            goldDrop: GoldAmount::fromGold(1),
            damageType: DamageType::Physical,
            baseDamage: 40,
            attackSpeed: 1.8
        );
        $this->addAbility('backstab', ['damage_multiplier' => 2.0, 'requires_stealth' => true]);
        $this->addAbility('kick', ['interrupt_duration' => 2]);
    }
}