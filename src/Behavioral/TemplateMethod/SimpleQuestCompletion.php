<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\TemplateMethod;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

class SimpleQuestCompletion extends QuestCompletionTemplate
{
    private string $questId;
    private string $questTitle;
    private int $requiredLevel;
    private int $experienceReward;
    private GoldAmount $goldReward;
    private ?Item $itemReward;
    private \Closure $objectiveCheck;

    public function __construct(
        string $questId,
        string $questTitle,
        int $requiredLevel,
        int $experienceReward,
        GoldAmount $goldReward,
        \Closure $objectiveCheck,
        ?Item $itemReward = null
    ) {
        $this->questId = $questId;
        $this->questTitle = $questTitle;
        $this->requiredLevel = $requiredLevel;
        $this->experienceReward = $experienceReward;
        $this->goldReward = $goldReward;
        $this->itemReward = $itemReward;
        $this->objectiveCheck = $objectiveCheck;
    }

    protected function validateCompletion(PlayerCharacter $character): bool
    {
        return ($this->objectiveCheck)($character);
    }

    protected function calculateRewards(PlayerCharacter $character): QuestRewards
    {
        $levelMultiplier = 1 + ($character->getLevel() - $this->requiredLevel) * 0.05;
        $bonusExp = (int) ($this->experienceReward * $levelMultiplier);

        return new QuestRewards(
            experience: $bonusExp,
            gold: $this->goldReward,
            items: $this->itemReward ? [$this->itemReward] : []
        );
    }

    protected function canStart(PlayerCharacter $character): bool
    {
        return $character->getLevel() >= $this->requiredLevel;
    }

    protected function onPostCompletion(PlayerCharacter $character, QuestRewards $rewards): void
    {
        echo "Quest completed: {$this->questTitle}\n";
        echo "Rewards: {$rewards->experience} XP, {$rewards->gold}\n";
        if (!empty($rewards->items)) {
            foreach ($rewards->items as $item) {
                echo "Item: {$item->name}\n";
            }
        }
    }
}