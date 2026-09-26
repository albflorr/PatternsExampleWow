<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\Repository;

use AzerothEngine\Domain\Model\PlayerCharacter;

/** @var PlayerCharacter[] */
class InMemoryPlayerRepository implements PlayerRepositoryInterface
{
    private array $players = [];

    public function save(PlayerCharacter $character): void
    {
        $this->players[$character->getId()] = $character;
    }

    public function findById(string $id): ?PlayerCharacter
    {
        return $this->players[$id] ?? null;
    }

    public function findByName(string $name): ?PlayerCharacter
    {
        foreach ($this->players as $player) {
            if ($player->getName() === $name) {
                return $player;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return array_values($this->players);
    }

    public function delete(string $id): void
    {
        unset($this->players[$id]);
    }

    public function exists(string $id): bool
    {
        return isset($this->players[$id]);
    }
}