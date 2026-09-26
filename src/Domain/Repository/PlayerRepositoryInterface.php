<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Repository;

use AzerothEngine\Domain\Model\PlayerCharacter;

interface PlayerRepositoryInterface
{
    public function save(PlayerCharacter $character): void;
    public function findById(string $id): ?PlayerCharacter;
    public function findByName(string $name): ?PlayerCharacter;
    public function findAll(): array;
    public function delete(string $id): void;
    public function exists(string $id): bool;
}