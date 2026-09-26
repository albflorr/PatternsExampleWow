<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Singleton;

final class EngineConfig
{
    private static ?EngineConfig $instance = null;

    private array $config = [];

    private function __construct()
    {
        $this->config = [
            'game_version' => '1.0.0',
            'max_level' => 60,
            'base_experience_rate' => 1.0,
            'base_drop_rate' => 1.0,
            'pvp_enabled' => true,
            'cross_faction_chat' => false,
            'max_group_size' => 5,
            'max_raid_size' => 40,
            'auction_house_cut' => 0.05,
            'mail_delivery_time_hours' => 1,
            'rested_xp_bonus' => 0.5,
            'durability_loss_on_death' => 0.10,
            'resurrection_sickness_duration_minutes' => 10,
        ];
    }

    public static function getInstance(): EngineConfig
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    public function getAll(): array
    {
        return $this->config;
    }

    public function getMaxLevel(): int
    {
        return (int) $this->config['max_level'];
    }

    public function isPvpEnabled(): bool
    {
        return (bool) $this->config['pvp_enabled'];
    }

    public function getExperienceRate(): float
    {
        return (float) $this->config['base_experience_rate'];
    }
}