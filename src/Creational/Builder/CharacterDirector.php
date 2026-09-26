<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Builder;

use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Skill;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;

class CharacterDirector
{
    public function createStarterCharacter(CharacterBuilder $builder, string $name, Race $race, CharacterClass $class): PlayerCharacter
    {
        return $builder
            ->setName($name)
            ->setRace($race)
            ->setClass($class)
            ->setLevel(1)
            ->setGold(GoldAmount::fromSilver(50))
            ->addSkills($this->getStarterSkills($class))
            ->build();
    }

    public function createVeteranCharacter(CharacterBuilder $builder, string $name, Race $race, CharacterClass $class, int $level): PlayerCharacter
    {
        return $builder
            ->setName($name)
            ->setRace($race)
            ->setClass($class)
            ->setLevel($level)
            ->setGold(GoldAmount::fromGold($level * 10))
            ->addSkills($this->getAllSkillsForLevel($class, $level))
            ->equipFullSet($race->getFaction(), $level)
            ->build();
    }

    public function createTwinkCharacter(CharacterBuilder $builder, string $name, Race $race, CharacterClass $class, int $level): PlayerCharacter
    {
        return $builder
            ->setName($name)
            ->setRace($race)
            ->setClass($class)
            ->setLevel($level)
            ->setGold(GoldAmount::fromGold(1000))
            ->addSkills($this->getAllSkillsForLevel($class, $level))
            ->equipFullSet($race->getFaction(), $level + 5)
            ->addCustomAttributes(new Attributes(strength: 50, agility: 50, intellect: 50, stamina: 50, spirit: 50))
            ->build();
    }

    private function getStarterSkills(CharacterClass $class): array
    {
        return match ($class) {
            CharacterClass::Warrior => [Skill::warriorSkills()[0]],
            CharacterClass::Mage => [Skill::mageSkills()[0]],
            CharacterClass::Hunter => [Skill::hunterSkills()[0]],
        };
    }

    private function getAllSkillsForLevel(CharacterClass $class, int $level): array
    {
        $allSkills = match ($class) {
            CharacterClass::Warrior => Skill::warriorSkills(),
            CharacterClass::Mage => Skill::mageSkills(),
            CharacterClass::Hunter => Skill::hunterSkills(),
        };

        return array_filter($allSkills, fn($skill) => $skill->requiredLevel <= $level);
    }
}