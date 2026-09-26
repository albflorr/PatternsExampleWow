<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Prototype;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\HealthPoints;
use AzerothEngine\Domain\ValueObject\ManaPoints;

class DragonkinPrototype extends MonsterPrototype
{
    public function __construct()
    {
        parent::__construct(
            id: 'dragonkin_prototype',
            name: 'Young Dragon',
            level: 40,
            race: Race::Orc,
            health: HealthPoints::full(5000),
            mana: ManaPoints::full(1000),
            attributes: new Attributes(strength: 80, agility: 40, intellect: 60, stamina: 100),
            experienceReward: 5000,
            goldDrop: GoldAmount::fromGold(50),
            damageType: DamageType::Fire,
            baseDamage: 200,
            attackSpeed: 2.5
        );
        $this->addAbility('fire_breath', ['damage_multiplier' => 3.0, 'damage_type' => DamageType::Fire, 'aoe' => true]);
        $this->addAbility('wing_buffet', ['knockback_distance' => 10]);
    }
}