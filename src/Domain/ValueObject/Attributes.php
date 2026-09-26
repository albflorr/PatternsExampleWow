<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

use AzerothEngine\Domain\Enum\CharacterClass;

readonly class Attributes
{
    public function __construct(
        public int $strength = 0,
        public int $agility = 0,
        public int $intellect = 0,
        public int $stamina = 0,
        public int $spirit = 0,
        public int $armor = 0,
        public int $fireResistance = 0,
        public int $frostResistance = 0,
        public int $arcaneResistance = 0,
        public int $shadowResistance = 0,
        public int $natureResistance = 0,
        public int $holyResistance = 0
    ) {
        if ($strength < 0 || $agility < 0 || $intellect < 0 || $stamina < 0 || $spirit < 0) {
            throw new \InvalidArgumentException('Primary attributes cannot be negative');
        }
    }

    public static function base(CharacterClass $class): self
    {
        return match ($class) {
            CharacterClass::Warrior => new self(strength: 20, agility: 10, intellect: 5, stamina: 15, spirit: 5),
            CharacterClass::Mage => new self(strength: 5, agility: 10, intellect: 25, stamina: 8, spirit: 15),
            CharacterClass::Hunter => new self(strength: 10, agility: 20, intellect: 10, stamina: 10, spirit: 10),
        };
    }

    public function add(Attributes $other): self
    {
        return new self(
            strength: $this->strength + $other->strength,
            agility: $this->agility + $other->agility,
            intellect: $this->intellect + $other->intellect,
            stamina: $this->stamina + $other->stamina,
            spirit: $this->spirit + $other->spirit,
            armor: $this->armor + $other->armor,
            fireResistance: $this->fireResistance + $other->fireResistance,
            frostResistance: $this->frostResistance + $other->frostResistance,
            arcaneResistance: $this->arcaneResistance + $other->arcaneResistance,
            shadowResistance: $this->shadowResistance + $other->shadowResistance,
            natureResistance: $this->natureResistance + $other->natureResistance,
            holyResistance: $this->holyResistance + $other->holyResistance,
        );
    }

    public function getPrimaryForClass(CharacterClass $class): int
    {
        return match ($class->getPrimaryAttribute()) {
            'strength' => $this->strength,
            'agility' => $this->agility,
            'intellect' => $this->intellect,
            default => 0,
        };
    }

    public function getHealthBonus(): int
    {
        return $this->stamina * 10;
    }

    public function getManaBonus(): int
    {
        return $this->intellect * 15;
    }

    public function getAttackPower(): int
    {
        return $this->strength * 2 + $this->agility;
    }

    public function getSpellPower(): int
    {
        return $this->intellect + $this->spirit;
    }

    public function getCritChance(): float
    {
        return $this->agility * 0.05;
    }

    public function __toString(): string
    {
        return "Str: {$this->strength}, Agi: {$this->agility}, Int: {$this->intellect}, Sta: {$this->stamina}, Spi: {$this->spirit}";
    }
}