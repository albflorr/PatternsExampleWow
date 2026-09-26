<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

interface StorageStrategyInterface
{
    public function save(string $key, mixed $data): bool;
    public function load(string $key): mixed;
    public function delete(string $key): bool;
    public function exists(string $key): bool;
    public function getName(): string;
}