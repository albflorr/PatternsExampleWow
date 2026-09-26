<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Composite;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Quest;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

interface QuestComponentInterface
{
    public function getId(): string;
    public function getTitle(): string;
    public function getDescription(): string;
    public function canStart(PlayerCharacter $character): bool;
    public function isComplete(PlayerCharacter $character): bool;
    public function getProgress(PlayerCharacter $character): string;
    public function getRewardGold(): GoldAmount;
    public function getExperienceReward(): int;
    public function getRewardItem(): ?Item;
    public function markCompleted(): void;
    public function isCompleted(): bool;
}

abstract class QuestComponent implements QuestComponentInterface
{
    protected string $id;
    protected string $title;
    protected string $description;
    protected GoldAmount $rewardGold;
    protected int $experienceReward;
    protected ?Item $rewardItem;
    protected bool $isCompleted = false;

    public function __construct(
        string $id,
        string $title,
        string $description,
        GoldAmount $rewardGold,
        int $experienceReward,
        ?Item $rewardItem = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
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

    public function markCompleted(): void
    {
        $this->isCompleted = true;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    abstract public function canStart(PlayerCharacter $character): bool;
    abstract public function isComplete(PlayerCharacter $character): bool;
    abstract public function getProgress(PlayerCharacter $character): string;
}

class QuestObjective extends QuestComponent
{
    private \Closure $completionCheck;
    private \Closure $progressCheck;
    private int $requiredLevel;

    public function __construct(
        string $id,
        string $title,
        string $description,
        int $requiredLevel,
        GoldAmount $rewardGold,
        int $experienceReward,
        \Closure $completionCheck,
        \Closure $progressCheck,
        ?Item $rewardItem = null
    ) {
        parent::__construct($id, $title, $description, $rewardGold, $experienceReward, $rewardItem);
        $this->requiredLevel = $requiredLevel;
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

class QuestComposite extends QuestComponent
{
    /** @var QuestComponentInterface[] */
    private array $children = [];

    public function __construct(
        string $id,
        string $title,
        string $description,
        GoldAmount $rewardGold,
        int $experienceReward,
        ?Item $rewardItem = null
    ) {
        parent::__construct($id, $title, $description, $rewardGold, $experienceReward, $rewardItem);
    }

    public function add(QuestComponentInterface $component): void
    {
        $this->children[] = $component;
    }

    public function remove(QuestComponentInterface $component): void
    {
        $this->children = array_filter($this->children, fn($c) => $c->getId() !== $component->getId());
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function canStart(PlayerCharacter $character): bool
    {
        foreach ($this->children as $child) {
            if (!$child->canStart($character)) {
                return false;
            }
        }
        return true;
    }

    public function isComplete(PlayerCharacter $character): bool
    {
        foreach ($this->children as $child) {
            if (!$child->isComplete($character)) {
                return false;
            }
        }
        return true;
    }

    public function getProgress(PlayerCharacter $character): string
    {
        $completed = 0;
        $total = count($this->children);

        foreach ($this->children as $child) {
            if ($child->isComplete($character)) {
                $completed++;
            }
        }

        $details = [];
        foreach ($this->children as $child) {
            $status = $child->isComplete($character) ? '[x]' : '[ ]';
            $details[] = "  {$status} {$child->getTitle()}: {$child->getProgress($character)}";
        }

        return "Progress: {$completed}/{$total} objectives\n" . implode("\n", $details);
    }
}

class QuestLog
{
    /** @var QuestComponentInterface[] */
    private array $quests = [];

    public function addQuest(QuestComponentInterface $quest): void
    {
        $this->quests[$quest->getId()] = $quest;
    }

    public function removeQuest(string $questId): void
    {
        unset($this->quests[$questId]);
    }

    public function getQuest(string $questId): ?QuestComponentInterface
    {
        return $this->quests[$questId] ?? null;
    }

    public function getActiveQuests(): array
    {
        return array_filter($this->quests, fn($q) => !$q->isCompleted());
    }

    public function getCompletedQuests(): array
    {
        return array_filter($this->quests, fn($q) => $q->isCompleted());
    }

    public function getAvailableQuests(PlayerCharacter $character): array
    {
        return array_filter($this->quests, fn($q) => !$q->isCompleted() && $q->canStart($character));
    }

    public function getQuestProgress(PlayerCharacter $character): string
    {
        $output = "=== QUEST LOG ===\n";
        foreach ($this->quests as $quest) {
            $status = $quest->isCompleted() ? '[COMPLETED]' : '  [ACTIVE]  ';
            $output .= "{$status} {$quest->getTitle()}\n";
            $output .= "  {$quest->getDescription()}\n";
            $output .= "  {$quest->getProgress($character)}\n";
            $output .= "  Rewards: {$quest->getExperienceReward()} XP, {$quest->getRewardGold()}\n";
            if ($quest->getRewardItem()) {
                $output .= "  Item: {$quest->getRewardItem()->name}\n";
            }
            $output .= "\n";
        }
        return $output;
    }

    public function completeQuest(string $questId, PlayerCharacter $character): bool
    {
        $quest = $this->getQuest($questId);
        if ($quest === null || $quest->isCompleted()) {
            return false;
        }

        if ($quest->isComplete($character)) {
            $quest->markCompleted();
            $character->addExperience($quest->getExperienceReward());
            $character->addGold($quest->getRewardGold());
            if ($quest->getRewardItem()) {
            }
            return true;
        }

        return false;
    }

    public function __toString(): string
    {
        return $this->getQuestProgress(new \AzerothEngine\Domain\Model\PlayerCharacter(
            'temp', 'temp', \AzerothEngine\Domain\Enum\CharacterClass::Warrior, \AzerothEngine\Domain\Enum\Race::Human
        ));
    }
}