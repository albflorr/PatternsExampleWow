<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Prototype;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\HealthPoints;
use AzerothEngine\Domain\ValueObject\ManaPoints;

class UndeadPrototype extends MonsterPrototype
{
    public function __construct()
    {
        parent::__construct(
            id: 'undead_prototype',
            name: 'Risen Dead',
            level: 15,
            race: Race::Undead,
            health: HealthPoints::full(800),
            mana: ManaPoints::full(200),
            attributes: new Attributes(strength: 25, stamina: 30, intellect: 15),
            experienceReward: 500,
            goldDrop: GoldAmount::fromGold(2),
            damageType: DamageType::Shadow,
            baseDamage: 50,
            attackSpeed: 1.5
        );
        $this->addAbility('shadow_bolt', ['damage_multiplier' => 1.5, 'damage_type' => DamageType::Shadow]);
        $this->addAbility('fear', ['duration' => 4]);
    }
}