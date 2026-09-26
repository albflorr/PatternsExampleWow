<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Prototype;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\HealthPoints;
use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemLevel;
use AzerothEngine\Domain\ValueObject\ItemQuality;
use AzerothEngine\Domain\ValueObject\ItemSlot;
use AzerothEngine\Domain\ValueObject\ManaPoints;

interface PrototypeInterface
{
    public function clone(): self;
}

abstract class MonsterPrototype implements PrototypeInterface
{
    protected string $id;
    protected string $name;
    protected int $level;
    protected Race $race;
    protected HealthPoints $health;
    protected ManaPoints $mana;
    protected Attributes $attributes;
    protected int $experienceReward;
    protected GoldAmount $goldDrop;
    /** @var array<string, mixed> */
    protected array $lootTable = [];
    protected DamageType $damageType;
    protected int $baseDamage;
    protected float $attackSpeed;
    protected array $abilities = [];

    public function __construct(
        string $id,
        string $name,
        int $level,
        Race $race,
        HealthPoints $health,
        ManaPoints $mana,
        Attributes $attributes,
        int $experienceReward,
        GoldAmount $goldDrop,
        DamageType $damageType,
        int $baseDamage,
        float $attackSpeed
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->race = $race;
        $this->health = $health;
        $this->mana = $mana;
        $this->attributes = $attributes;
        $this->experienceReward = $experienceReward;
        $this->goldDrop = $goldDrop;
        $this->damageType = $damageType;
        $this->baseDamage = $baseDamage;
        $this->attackSpeed = $attackSpeed;
    }

    public function clone(): self
    {
        $cloned = clone $this;
        $cloned->id = $this->generateUniqueId();
        $cloned->health = HealthPoints::full($this->health->maximum);
        $cloned->mana = ManaPoints::full($this->mana->maximum);
        $cloned->lootTable = $this->lootTable;
        $cloned->abilities = $this->abilities;
        return $cloned;
    }

    protected function generateUniqueId(): string
    {
        return $this->id . '_' . uniqid();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setBaseDamage(int $baseDamage): void
    {
        $this->baseDamage = $baseDamage;
    }

    public function setHealth(HealthPoints $health): void
    {
        $this->health = $health;
    }

    public function getRace(): Race
    {
        return $this->race;
    }

    public function getHealth(): HealthPoints
    {
        return $this->health;
    }

    public function getMana(): ManaPoints
    {
        return $this->mana;
    }

    public function getAttributes(): Attributes
    {
        return $this->attributes;
    }

    public function getExperienceReward(): int
    {
        return $this->experienceReward;
    }

    public function getGoldDrop(): GoldAmount
    {
        return $this->goldDrop;
    }

    public function getLootTable(): array
    {
        return $this->lootTable;
    }

    public function getDamageType(): DamageType
    {
        return $this->damageType;
    }

    public function getBaseDamage(): int
    {
        return $this->baseDamage;
    }

    public function getAttackSpeed(): float
    {
        return $this->attackSpeed;
    }

    public function getAbilities(): array
    {
        return $this->abilities;
    }

    public function addLoot(string $itemId, float $dropChance, int $minCount = 1, int $maxCount = 1): void
    {
        $this->lootTable[$itemId] = ['chance' => $dropChance, 'min' => $minCount, 'max' => $maxCount];
    }

    public function addAbility(string $abilityId, array $params = []): void
    {
        $this->abilities[$abilityId] = $params;
    }

    public function takeDamage(int $amount): void
    {
        $this->health = $this->health->damage($amount);
    }

    public function isDead(): bool
    {
        return $this->health->isEmpty();
    }

    public function __toString(): string
    {
        return "{$this->name} (Level {$this->level} {$this->race->value}) - HP: {$this->health}";
    }
}