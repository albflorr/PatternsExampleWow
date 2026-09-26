<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\TemplateMethod;

use AzerothEngine\Domain\ValueObject\GoldAmount;

class QuestRewards
{
    public function __construct(
        public int $experience = 0,
        public ?GoldAmount $gold = null,
        public array $items = [],
        public array $reputation = []
    ) {
        $this->gold = $gold ?? GoldAmount::fromGold(0);
    }

    public function add(QuestRewards $other): self
    {
        return new self(
            experience: $this->experience + $other->experience,
            gold: $this->gold->add($other->gold),
            items: array_merge($this->items, $other->items),
            reputation: array_merge($this->reputation, $other->reputation)
        );
    }
}