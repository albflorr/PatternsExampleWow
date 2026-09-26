<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Builder;

use AzerothEngine\Creational\Singleton\GameWorld;
use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Skill;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemLevel;
use AzerothEngine\Domain\ValueObject\ItemQuality;
use AzerothEngine\Domain\ValueObject\ItemSlot;

class CharacterBuilder
{
    private string $name = '';
    private ?Race $race = null;
    private ?CharacterClass $class = null;
    private int $level = 1;
    private ?GoldAmount $gold = null;
    private array $skills = [];
    private array $equipment = [];
    private array $talents = [];
    private Attributes $customAttributes;

    public function __construct()
    {
        $this->gold = GoldAmount::fromGold(0);
        $this->customAttributes = new Attributes();
    }

    public function setName(string $name): self
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Character name cannot be empty');
        }
        $this->name = $name;
        return $this;
    }

    public function setRace(Race $race): self
    {
        $this->race = $race;
        return $this;
    }

    public function setClass(CharacterClass $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function setLevel(int $level): self
    {
        if ($level < 1 || $level > 60) {
            throw new \InvalidArgumentException('Level must be between 1 and 60');
        }
        $this->level = $level;
        return $this;
    }

    public function setGold(GoldAmount $gold): self
    {
        $this->gold = $gold;
        return $this;
    }

    public function addSkill(Skill $skill): self
    {
        $this->skills[] = $skill;
        return $this;
    }

    public function addSkills(array $skills): self
    {
        foreach ($skills as $skill) {
            $this->addSkill($skill);
        }
        return $this;
    }

    public function addTalent(string $talentId, int $rank): self
    {
        $this->talents[$talentId] = $rank;
        return $this;
    }

    public function addCustomAttributes(Attributes $attributes): self
    {
        $this->customAttributes = $this->customAttributes->add($attributes);
        return $this;
    }

    public function equipItem(Item $item): self
    {
        $this->equipment[$item->slot->value] = $item;
        return $this;
    }

    public function equipFullSet(string $faction, int $level): self
    {
        $set = \AzerothEngine\Creational\Factory\EquipmentFactoryRegistry::createFullSet($faction, $level);
        foreach ($set as $item) {
            $this->equipItem($item);
        }
        return $this;
    }

    public function build(): PlayerCharacter
    {
        $this->validate();

        $id = $this->generateId();
        $character = new PlayerCharacter(
            id: $id,
            name: $this->name,
            class: $this->class,
            race: $this->race,
            level: $this->level,
            gold: $this->gold
        );

        foreach ($this->skills as $skill) {
            if ($skill->canCast($character)) {
                // Skills are stored in the character's spellbook
            }
        }

        foreach ($this->equipment as $item) {
            $character->equipItem($item);
        }

        if ($this->level > 1) {
            $character->addExperience(($this->level - 1) * 1000);
        }

        GameWorld::getInstance()->addPlayer($character);

        return $character;
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Character name is required');
        }
        if ($this->race === null) {
            throw new \InvalidArgumentException('Race is required');
        }
        if ($this->class === null) {
            throw new \InvalidArgumentException('Class is required');
        }

        $validCombinations = [
            Race::Orc->value => [CharacterClass::Warrior, CharacterClass::Hunter],
            Race::Undead->value => [CharacterClass::Warrior, CharacterClass::Mage],
            Race::Human->value => [CharacterClass::Warrior, CharacterClass::Mage],
            Race::NightElf->value => [CharacterClass::Warrior, CharacterClass::Hunter],
        ];

        if (!in_array($this->class, $validCombinations[$this->race->value] ?? [])) {
            throw new \InvalidArgumentException("Class {$this->class->value} is not available for {$this->race->value}");
        }
    }

    private function generateId(): string
    {
        return strtolower("{$this->race->value}_{$this->class->value}_" . uniqid());
    }
}