<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\TemplateMethod;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

abstract class QuestCompletionTemplate
{
    final public function completeQuest(PlayerCharacter $character): QuestCompletionResult
    {
        if (!$this->canStart($character)) {
            return new QuestCompletionResult(false, 'Character does not meet requirements');
        }

        $this->onPreCompletion($character);

        if (!$this->validateCompletion($character)) {
            return new QuestCompletionResult(false, 'Quest objectives not completed');
        }

        $rewards = $this->calculateRewards($character);
        $this->grantRewards($character, $rewards);
        $this->onPostCompletion($character, $rewards);

        return new QuestCompletionResult(true, 'Quest completed successfully', $rewards);
    }

    protected function onPreCompletion(PlayerCharacter $character): void
    {
    }

    abstract protected function validateCompletion(PlayerCharacter $character): bool;

    abstract protected function calculateRewards(PlayerCharacter $character): QuestRewards;

    protected function grantRewards(PlayerCharacter $character, QuestRewards $rewards): void
    {
        $character->addExperience($rewards->experience);
        $character->addGold($rewards->gold);

        foreach ($rewards->items as $item) {
        }
    }

    protected function onPostCompletion(PlayerCharacter $character, QuestRewards $rewards): void
    {
    }

    abstract protected function canStart(PlayerCharacter $character): bool;
}