<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Model;

use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\HealthPoints;
use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemSlot;
use AzerothEngine\Domain\ValueObject\ManaPoints;
use AzerothEngine\Domain\ValueObject\ItemId;

class PlayerCharacter
{
    private string $id;
    private string $name;
    private CharacterClass $class;
    private Race $race;
    private int $level;
    private int $experience;
    private HealthPoints $health;
    private ManaPoints $mana;
    private Attributes $baseAttributes;
    private Attributes $equipmentAttributes;
    /** @var array<ItemSlot, Item|null> */
    private array $equipment = [];
    private GoldAmount $gold;
    private CharacterState $state;
    private array $activeEffects = [];

    public function __construct(
        string $id,
        string $name,
        CharacterClass $class,
        Race $race,
        int $level = 1,
        int $experience = 0,
        ?GoldAmount $gold = null,
        ?CharacterState $state = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->class = $class;
        $this->race = $race;
        $this->level = $level;
        $this->experience = $experience;
        $this->gold = $gold ?? GoldAmount::fromGold(0);
        $this->state = $state ?? CharacterState::Normal;
        $this->baseAttributes = Attributes::base($class)->add(new Attributes(...$race->getRacialBonus()));
        $this->equipmentAttributes = new Attributes();
        $this->health = HealthPoints::full($this->calculateMaxHealth());
        $this->mana = ManaPoints::full($this->calculateMaxMana());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClass(): CharacterClass
    {
        return $this->class;
    }

    public function getRace(): Race
    {
        return $this->race;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getExperience(): int
    {
        return $this->experience;
    }

    public function getHealth(): HealthPoints
    {
        return $this->health;
    }

    public function getMana(): ManaPoints
    {
        return $this->mana;
    }

    public function getGold(): GoldAmount
    {
        return $this->gold;
    }

    public function getState(): CharacterState
    {
        return $this->state;
    }

    public function getTotalAttributes(): Attributes
    {
        return $this->baseAttributes->add($this->equipmentAttributes);
    }

    public function getBaseAttributes(): Attributes
    {
        return $this->baseAttributes;
    }

    public function getEquipmentAttributes(): Attributes
    {
        return $this->equipmentAttributes;
    }

    /** @return array<ItemSlot, Item|null> */
    public function getEquipment(): array
    {
        return $this->equipment;
    }

    public function getEquippedItem(ItemSlot $slot): ?Item
    {
        return $this->equipment[$slot->value] ?? null;
    }

    public function equipItem(Item $item): void
    {
        if (!$item->isEquippable) {
            throw new \InvalidArgumentException('Item is not equippable');
        }

        $this->unequipSlot($item->slot);
        $this->equipment[$item->slot->value] = $item;
        $this->recalculateEquipmentAttributes();
        $this->updateHealthAndMana();
    }

    public function unequipSlot(ItemSlot $slot): ?Item
    {
        $item = $this->equipment[$slot->value] ?? null;
        if ($item !== null) {
            unset($this->equipment[$slot->value]);
            $this->recalculateEquipmentAttributes();
            $this->updateHealthAndMana();
        }
        return $item;
    }

    private function recalculateEquipmentAttributes(): void
    {
        $attrs = new Attributes();
        foreach ($this->equipment as $item) {
            if ($item !== null) {
                $attrs = $attrs->add($item->attributes);
            }
        }
        $this->equipmentAttributes = $attrs;
    }

    private function updateHealthAndMana(): void
    {
        $maxHealth = $this->calculateMaxHealth();
        $maxMana = $this->calculateMaxMana();

        $this->health = $this->health->withMaximum($maxHealth);
        $this->mana = $this->mana->withMaximum($maxMana);
    }

    private function calculateMaxHealth(): int
    {
        return $this->class->getBaseHealth() + $this->getTotalAttributes()->getHealthBonus() + ($this->level - 1) * 10;
    }

    private function calculateMaxMana(): int
    {
        return $this->class->getBaseMana() + $this->getTotalAttributes()->getManaBonus() + ($this->level - 1) * 5;
    }

    public function addExperience(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Experience cannot be negative');
        }
        $this->experience += $amount;
        $this->checkLevelUp();
    }

    private function checkLevelUp(): void
    {
        $requiredExp = $this->level * 1000;
        while ($this->experience >= $requiredExp) {
            $this->levelUp();
            $requiredExp = $this->level * 1000;
        }
    }

    private function levelUp(): void
    {
        $this->level++;
        $this->health = HealthPoints::full($this->calculateMaxHealth());
        $this->mana = ManaPoints::full($this->calculateMaxMana());
    }

    public function takeDamage(int $amount): void
    {
        if ($this->state === CharacterState::Dead) {
            return;
        }
        $this->health = $this->health->damage($amount);
        if ($this->health->isEmpty()) {
            $this->die();
        }
    }

    public function heal(int $amount): void
    {
        $this->health = $this->health->heal($amount);
    }

    public function spendMana(int $amount): void
    {
        $this->mana = $this->mana->spend($amount);
    }

    public function restoreMana(int $amount): void
    {
        $this->mana = $this->mana->restore($amount);
    }

    public function addGold(GoldAmount $amount): void
    {
        $this->gold = $this->gold->add($amount);
    }

    public function spendGold(GoldAmount $amount): void
    {
        $this->gold = $this->gold->subtract($amount);
    }

    public function setState(CharacterState $state): void
    {
        $this->state = $state;
        if ($state === CharacterState::Dead) {
            $this->health = HealthPoints::empty($this->health->maximum);
        }
    }

    public function die(): void
    {
        $this->state = CharacterState::Dead;
        $this->health = HealthPoints::empty($this->health->maximum);
    }

    public function revive(): void
    {
        $this->state = CharacterState::Normal;
        $this->health = $this->health->withCurrent(1);
    }

    public function canAct(): bool
    {
        return $this->state->canAct();
    }

    public function applyEffect(string $effectName, int $duration): void
    {
        $this->activeEffects[$effectName] = $duration;
    }

    public function removeEffect(string $effectName): void
    {
        unset($this->activeEffects[$effectName]);
    }

    public function hasEffect(string $effectName): bool
    {
        return isset($this->activeEffects[$effectName]);
    }

    public function tickEffects(): void
    {
        foreach ($this->activeEffects as $name => $duration) {
            $newDuration = $duration - 1;
            if ($newDuration <= 0) {
                unset($this->activeEffects[$name]);
            } else {
                $this->activeEffects[$name] = $newDuration;
            }
        }
    }

    public function getAttackPower(): int
    {
        return $this->getTotalAttributes()->getAttackPower();
    }

    public function getSpellPower(): int
    {
        return $this->getTotalAttributes()->getSpellPower();
    }

    public function getCritChance(): float
    {
        return $this->getTotalAttributes()->getCritChance();
    }

    public function __toString(): string
    {
        return "{$this->name} (Level {$this->level} {$this->race->value} {$this->class->value}) - HP: {$this->health}, Mana: {$this->mana}, Gold: {$this->gold}";
    }
}