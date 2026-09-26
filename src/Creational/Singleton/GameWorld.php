<?php

declare(strict_types=1);

namespace AzerothEngine\Creational\Singleton;

use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\Model\PlayerCharacter;

final class GameWorld
{
    private static ?GameWorld $instance = null;

    private array $onlineCharacters = [];
    private array $zonePlayers = [];
    private string $serverTime;
    private int $maxPlayers = 10000;
    private bool $maintenanceMode = false;

    private function __construct()
    {
        $this->serverTime = date('Y-m-d H:i:s');
    }

    public static function getInstance(): GameWorld
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

    public function addPlayer(PlayerCharacter $character): void
    {
        if (count($this->onlineCharacters) >= $this->maxPlayers) {
            throw new \RuntimeException('Server is full');
        }
        $this->onlineCharacters[$character->getId()] = $character;
        $this->zonePlayers['starting_zone'][] = $character->getId();
    }

    public function removePlayer(string $characterId): void
    {
        unset($this->onlineCharacters[$characterId]);
        foreach ($this->zonePlayers as $zone => $players) {
            $this->zonePlayers[$zone] = array_filter($players, fn($id) => $id !== $characterId);
        }
    }

    public function getPlayer(string $characterId): ?PlayerCharacter
    {
        return $this->onlineCharacters[$characterId] ?? null;
    }

    public function getOnlineCount(): int
    {
        return count($this->onlineCharacters);
    }

    public function getPlayersInZone(string $zone): array
    {
        $playerIds = $this->zonePlayers[$zone] ?? [];
        return array_map(
            fn($id) => $this->onlineCharacters[$id],
            array_filter($playerIds, fn($id) => isset($this->onlineCharacters[$id]))
        );
    }

    public function movePlayerToZone(string $characterId, string $zone): void
    {
        foreach ($this->zonePlayers as $z => $players) {
            $this->zonePlayers[$z] = array_filter($players, fn($id) => $id !== $characterId);
        }
        $this->zonePlayers[$zone][] = $characterId;
    }

    public function isMaintenanceMode(): bool
    {
        return $this->maintenanceMode;
    }

    public function setMaintenanceMode(bool $mode): void
    {
        $this->maintenanceMode = $mode;
    }

    public function getServerTime(): string
    {
        return $this->serverTime;
    }

    public function broadcastMessage(string $message): void
    {
        foreach ($this->onlineCharacters as $character) {
            echo "[{$character->getName()}] {$message}\n";
        }
    }

    public function getFactionBalance(): array
    {
        $horde = 0;
        $alliance = 0;
        foreach ($this->onlineCharacters as $character) {
            if ($character->getRace()->getFaction() === 'horde') {
                $horde++;
            } else {
                $alliance++;
            }
        }
        return ['horde' => $horde, 'alliance' => $alliance];
    }
}