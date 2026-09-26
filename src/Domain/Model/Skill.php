<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Model;

use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\ValueObject\ManaPoints;

readonly class Skill
{
    public function __construct(
        public string $id,
        public string $name,
        public CharacterClass $requiredClass,
        public int $requiredLevel,
        public DamageType $damageType,
        public int $baseDamage,
        public int $manaCost,
        public float $coefficient,
        public int $cooldownSeconds,
        public string $description
    ) {}

    public function calculateDamage(PlayerCharacter $caster): int
    {
        $spellPower = $caster->getSpellPower();
        $attackPower = $caster->getAttackPower();

        return match ($this->damageType) {
            DamageType::Physical => (int) ($this->baseDamage + $attackPower * $this->coefficient),
            default => (int) ($this->baseDamage + $spellPower * $this->coefficient),
        };
    }

    public function canCast(PlayerCharacter $caster): bool
    {
        if ($caster->getClass() !== $this->requiredClass) {
            return false;
        }
        if ($caster->getLevel() < $this->requiredLevel) {
            return false;
        }
        if (!$caster->canAct()) {
            return false;
        }
        return $caster->getMana()->canAfford($this->manaCost);
    }

    public static function warriorSkills(): array
    {
        return [
            new self(
                'heroic_strike',
                'Heroic Strike',
                CharacterClass::Warrior,
                1,
                DamageType::Physical,
                20,
                0,
                1.5,
                3,
                'Instantly strike the target causing physical damage.'
            ),
            new self(
                'mortal_strike',
                'Mortal Strike',
                CharacterClass::Warrior,
                10,
                DamageType::Physical,
                80,
                0,
                2.0,
                6,
                'A vicious strike that deals physical damage and reduces healing effectiveness.'
            ),
            new self(
                'execute',
                'Execute',
                CharacterClass::Warrior,
                20,
                DamageType::Physical,
                150,
                0,
                2.5,
                15,
                'Attempt to finish off a wounded enemy, usable only on targets below 20% health.'
            ),
        ];
    }

    public static function mageSkills(): array
    {
        return [
            new self(
                'fireball',
                'Fireball',
                CharacterClass::Mage,
                1,
                DamageType::Fire,
                40,
                30,
                1.0,
                3,
                'Hurls a fiery ball that causes fire damage.'
            ),
            new self(
                'frostbolt',
                'Frostbolt',
                CharacterClass::Mage,
                4,
                DamageType::Frost,
                35,
                25,
                0.9,
                3,
                'Launches a bolt of frost at the enemy, causing frost damage and slowing movement.'
            ),
            new self(
                'arcane_explosion',
                'Arcane Explosion',
                CharacterClass::Mage,
                14,
                DamageType::Arcane,
                60,
                50,
                0.8,
                8,
                'Causes an explosion of arcane magic around the caster, damaging all nearby enemies.'
            ),
            new self(
                'pyroblast',
                'Pyroblast',
                CharacterClass::Mage,
                30,
                DamageType::Fire,
                300,
                120,
                2.0,
                30,
                'Hurls an immense fiery boulder that causes massive fire damage.'
            ),
        ];
    }

    public static function hunterSkills(): array
    {
        return [
            new self(
                'steady_shot',
                'Steady Shot',
                CharacterClass::Hunter,
                1,
                DamageType::Physical,
                25,
                15,
                1.2,
                2,
                'A steady shot that deals physical damage.'
            ),
            new self(
                'arcane_shot',
                'Arcane Shot',
                CharacterClass::Hunter,
                8,
                DamageType::Arcane,
                45,
                25,
                1.0,
                6,
                'An instant shot that deals arcane damage.'
            ),
            new self(
                'multi_shot',
                'Multi-Shot',
                CharacterClass::Hunter,
                18,
                DamageType::Physical,
                60,
                40,
                1.0,
                10,
                'Fires a shot at the target and up to 2 additional nearby enemies.'
            ),
            new self(
                'aimed_shot',
                'Aimed Shot',
                CharacterClass::Hunter,
                28,
                DamageType::Physical,
                200,
                60,
                2.5,
                15,
                'A focused shot that deals massive physical damage.'
            ),
        ];
    }
}