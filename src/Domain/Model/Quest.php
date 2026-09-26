<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Model;

use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

abstract class Quest
{
    protected string $id;
    protected string $title;
    protected string $description;
    protected int $requiredLevel;
    protected GoldAmount $rewardGold;
    protected ?Item $rewardItem;
    protected int $experienceReward;
    protected bool $isCompleted = false;

    public function __construct(
        string $id,
        string $title,
        string $description,
        int $requiredLevel,
        GoldAmount $rewardGold,
        int $experienceReward,
        ?Item $rewardItem = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->requiredLevel = $requiredLevel;
        $this->rewardGold = $rewardGold;
        $this->experienceReward = $experienceReward;
        $this->rewardItem = $rewardItem;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getRequiredLevel(): int
    {
        return $this->requiredLevel;
    }

    public function getRewardGold(): GoldAmount
    {
        return $this->rewardGold;
    }

    public function getExperienceReward(): int
    {
        return $this->experienceReward;
    }

    public function getRewardItem(): ?Item
    {
        return $this->rewardItem;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function markCompleted(): void
    {
        $this->isCompleted = true;
    }

    abstract public function canStart(PlayerCharacter $character): bool;
    abstract public function isComplete(PlayerCharacter $character): bool;
    abstract public function getProgress(PlayerCharacter $character): string;
}

class SimpleQuest extends Quest
{
    private callable $completionCheck;
    private callable $progressCheck;

    public function __construct(
        string $id,
        string $title,
        string $description,
        int $requiredLevel,
        GoldAmount $rewardGold,
        int $experienceReward,
        callable $completionCheck,
        callable $progressCheck,
        ?Item $rewardItem = null
    ) {
        parent::__construct($id, $title, $description, $requiredLevel, $rewardGold, $experienceReward, $rewardItem);
        $this->completionCheck = $completionCheck;
        $this->progressCheck = $progressCheck;
    }

    public function canStart(PlayerCharacter $character): bool
    {
        return $character->getLevel() >= $this->requiredLevel;
    }

    public function isComplete(PlayerCharacter $character): bool
    {
        return ($this->completionCheck)($character);
    }

    public function getProgress(PlayerCharacter $character): string
    {
        return ($this->progressCheck)($character);
    }
}

class CompositeQuest extends Quest
{
    /** @var Quest[] */
    private array $subQuests = [];

    public function __construct(
        string $id,
        string $title,
        string $description,
        int $requiredLevel,
        GoldAmount $rewardGold,
        int $experienceReward,
        ?Item $rewardItem = null
    ) {
        parent::__construct($id, $title, $description, $requiredLevel, $rewardGold, $experienceReward, $rewardItem);
    }

    public function addSubQuest(Quest $quest): void
    {
        $this->subQuests[] = $quest;
    }

    public function getSubQuests(): array
    {
        return $this->subQuests;
    }

    public function canStart(PlayerCharacter $character): bool
    {
        if ($character->getLevel() < $this->requiredLevel) {
            return false;
        }
        foreach ($this->subQuests as $subQuest) {
            if (!$subQuest->canStart($character)) {
                return false;
            }
        }
        return true;
    }

    public function isComplete(PlayerCharacter $character): bool
    {
        foreach ($this->subQuests as $subQuest) {
            if (!$subQuest->isComplete($character)) {
                return false;
            }
        }
        return true;
    }

    public function getProgress(PlayerCharacter $character): string
    {
        $completed = 0;
        $total = count($this->subQuests);

        foreach ($this->subQuests as $subQuest) {
            if ($subQuest->isComplete($character)) {
                $completed++;
            }
        }

        return "Progress: {$completed}/{$total} objectives completed";
    }
}