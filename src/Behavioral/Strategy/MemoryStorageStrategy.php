<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

class MemoryStorageStrategy implements StorageStrategyInterface
{
    private array $storage = [];

    public function save(string $key, mixed $data): bool
    {
        $this->storage[$key] = $data;
        return true;
    }

    public function load(string $key): mixed
    {
        return $this->storage[$key] ?? null;
    }

    public function delete(string $key): bool
    {
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);
            return true;
        }
        return false;
    }

    public function exists(string $key): bool
    {
        return isset($this->storage[$key]);
    }

    public function getName(): string
    {
        return 'Memory Storage';
    }
}