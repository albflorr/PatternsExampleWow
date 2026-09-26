<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\TemplateMethod;

class QuestCompletionResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?QuestRewards $rewards = null
    ) {}
}