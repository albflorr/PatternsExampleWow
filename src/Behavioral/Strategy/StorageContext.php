<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

class StorageContext
{
    private StorageStrategyInterface $strategy;

    public function __construct(?StorageStrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new MemoryStorageStrategy();
    }

    public function setStrategy(StorageStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function save(string $key, mixed $data): bool
    {
        return $this->strategy->save($key, $data);
    }

    public function load(string $key): mixed
    {
        return $this->strategy->load($key);
    }

    public function delete(string $key): bool
    {
        return $this->strategy->delete($key);
    }

    public function exists(string $key): bool
    {
        return $this->strategy->exists($key);
    }

    public function getStrategyName(): string
    {
        return $this->strategy->getName();
    }
}