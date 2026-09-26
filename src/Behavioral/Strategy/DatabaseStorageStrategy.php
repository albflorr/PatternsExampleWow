<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

use PDO;

class DatabaseStorageStrategy implements StorageStrategyInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->createTable();
    }

    private function createTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS storage (
            `key` VARCHAR(255) PRIMARY KEY,
            `value` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }

    public function save(string $key, mixed $data): bool
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $stmt = $this->pdo->prepare(
            "INSERT INTO storage (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        );
        return $stmt->execute([$key, $json]);
    }

    public function load(string $key): mixed
    {
        $stmt = $this->pdo->prepare("SELECT `value` FROM storage WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result ? json_decode((string) $result, true, 512, JSON_THROW_ON_ERROR) : null;
    }

    public function delete(string $key): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM storage WHERE `key` = ?");
        return $stmt->execute([$key]);
    }

    public function exists(string $key): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM storage WHERE `key` = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() !== false;
    }

    public function getName(): string
    {
        return 'Database Storage';
    }
}