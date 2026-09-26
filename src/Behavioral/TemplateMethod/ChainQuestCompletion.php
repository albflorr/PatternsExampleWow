<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\TemplateMethod;

use AzerothEngine\Domain\Model\PlayerCharacter;

class ChainQuestCompletion extends QuestCompletionTemplate
{
    /** @var QuestCompletionTemplate[] */
    private array $chain = [];
    private int $currentStep = 0;

    public function addStep(QuestCompletionTemplate $step): void
    {
        $this->chain[] = $step;
    }

    protected function validateCompletion(PlayerCharacter $character): bool
    {
        if ($this->currentStep >= count($this->chain)) {
            return true;
        }
        return $this->chain[$this->currentStep]->validateCompletion($character);
    }

    protected function calculateRewards(PlayerCharacter $character): QuestRewards
    {
        $totalRewards = new QuestRewards();
        foreach ($this->chain as $step) {
            $totalRewards = $totalRewards->add($step->calculateRewards($character));
        }
        return $totalRewards;
    }

    protected function canStart(PlayerCharacter $character): bool
    {
        return $this->currentStep < count($this->chain) && $this->chain[$this->currentStep]->canStart($character);
    }

    protected function onPostCompletion(PlayerCharacter $character, QuestRewards $rewards): void
    {
        if ($this->currentStep < count($this->chain)) {
            $step = $this->chain[$this->currentStep];
            $step->onPostCompletion($character, $rewards);
            $this->currentStep++;

            if ($this->currentStep < count($this->chain)) {
                echo "Next step available: " . get_class($this->chain[$this->currentStep]) . "\n";
            } else {
                echo "Quest chain completed!\n";
            }
        }
    }
}