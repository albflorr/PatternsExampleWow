<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\TemplateMethod;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

class DailyQuestCompletion extends QuestCompletionTemplate
{
    private string $questId;
    private string $title;
    private int $requiredLevel;
    private int $baseExperience;
    private GoldAmount $baseGold;
    private \Closure $objectiveCheck;
    private array $completedToday = [];

    public function __construct(
        string $questId,
        string $title,
        int $requiredLevel,
        int $baseExperience,
        GoldAmount $baseGold,
        \Closure $objectiveCheck
    ) {
        $this->questId = $questId;
        $this->title = $title;
        $this->requiredLevel = $requiredLevel;
        $this->baseExperience = $baseExperience;
        $this->baseGold = $baseGold;
        $this->objectiveCheck = $objectiveCheck;
    }

    protected function validateCompletion(PlayerCharacter $character): bool
    {
        $today = date('Y-m-d');
        if (isset($this->completedToday[$character->getId()][$today])) {
            return false;
        }
        return ($this->objectiveCheck)($character);
    }

    protected function calculateRewards(PlayerCharacter $character): QuestRewards
    {
        $bonusMultiplier = $character->getLevel() >= 60 ? 1.5 : 1.0;
        return new QuestRewards(
            experience: (int) ($this->baseExperience * $bonusMultiplier),
            gold: $this->baseGold
        );
    }

    protected function canStart(PlayerCharacter $character): bool
    {
        $today = date('Y-m-d');
        if (isset($this->completedToday[$character->getId()][$today])) {
            return false;
        }
        return $character->getLevel() >= $this->requiredLevel;
    }

    protected function onPostCompletion(PlayerCharacter $character, QuestRewards $rewards): void
    {
        $today = date('Y-m-d');
        $this->completedToday[$character->getId()][$today] = true;

        echo "Daily quest completed: {$this->title}\n";
        echo "Rewards: {$rewards->experience} XP, {$rewards->gold}\n";
        echo "Daily quest reset at midnight.\n";
    }
}