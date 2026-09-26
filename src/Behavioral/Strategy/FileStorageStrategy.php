<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Strategy;

class FileStorageStrategy implements StorageStrategyInterface
{
    private string $directory;

    public function __construct(string $directory = 'data')
    {
        $this->directory = $directory;
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    public function save(string $key, mixed $data): bool
    {
        $filePath = $this->directory . '/' . $this->sanitizeKey($key) . '.json';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        return file_put_contents($filePath, $json) !== false;
    }

    public function load(string $key): mixed
    {
        $filePath = $this->directory . '/' . $this->sanitizeKey($key) . '.json';
        if (!file_exists($filePath)) {
            return null;
        }
        $json = file_get_contents($filePath);
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    public function delete(string $key): bool
    {
        $filePath = $this->directory . '/' . $this->sanitizeKey($key) . '.json';
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public function exists(string $key): bool
    {
        $filePath = $this->directory . '/' . $this->sanitizeKey($key) . '.json';
        return file_exists($filePath);
    }

    public function getName(): string
    {
        return 'File Storage';
    }

    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
    }
}