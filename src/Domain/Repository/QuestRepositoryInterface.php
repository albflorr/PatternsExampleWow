<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Repository;

use AzerothEngine\Domain\Model\Quest;

interface QuestRepositoryInterface
{
    public function save(Quest $quest): void;
    public function findById(string $id): ?Quest;
    public function findByLevel(int $level): array;
    public function findAll(): array;
}