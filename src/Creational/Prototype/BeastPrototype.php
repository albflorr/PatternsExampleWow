<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Prototype;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\HealthPoints;
use AzerothEngine\Domain\ValueObject\ManaPoints;

class BeastPrototype extends MonsterPrototype
{
    public function __construct()
    {
        parent::__construct(
            id: 'beast_prototype',
            name: 'Wild Beast',
            level: 5,
            race: Race::Orc,
            health: HealthPoints::full(200),
            mana: ManaPoints::empty(),
            attributes: new Attributes(strength: 15, agility: 12, stamina: 10),
            experienceReward: 100,
            goldDrop: GoldAmount::fromSilver(20),
            damageType: DamageType::Physical,
            baseDamage: 25,
            attackSpeed: 2.0
        );
        $this->addAbility('bite', ['damage_multiplier' => 1.5]);
        $this->addAbility('roar', ['fear_duration' => 3]);
    }
}