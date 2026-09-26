<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Repository;

use AzerothEngine\Domain\Model\Quest;

/** @var Quest[] */
class InMemoryQuestRepository implements QuestRepositoryInterface
{
    private array $quests = [];

    public function save(Quest $quest): void
    {
        $this->quests[$quest->getId()] = $quest;
    }

    public function findById(string $id): ?Quest
    {
        return $this->quests[$id] ?? null;
    }

    public function findByLevel(int $level): array
    {
        return array_filter($this->quests, fn($q) => $q->getRequiredLevel() <= $level);
    }

    public function findAll(): array
    {
        return array_values($this->quests);
    }
}