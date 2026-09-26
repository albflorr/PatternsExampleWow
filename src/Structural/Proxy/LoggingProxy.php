<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Proxy;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

class LoggingProxy implements GameServiceInterface
{
    private GameServiceInterface $realService;

    public function __construct(GameServiceInterface $realService)
    {
        $this->realService = $realService;
    }

    public function enterZone(PlayerCharacter $character, string $zoneId): bool
    {
        $this->log("enterZone", ['character' => $character->getName(), 'zone' => $zoneId]);
        return $this->realService->enterZone($character, $zoneId);
    }

    public function purchaseItem(PlayerCharacter $character, Item $item): bool
    {
        $this->log("purchaseItem", ['character' => $character->getName(), 'item' => $item->name, 'price' => (string) $item->buyPrice]);
        return $this->realService->purchaseItem($character, $item);
    }

    public function accessBank(PlayerCharacter $character): array
    {
        $this->log("accessBank", ['character' => $character->getName()]);
        return $this->realService->accessBank($character);
    }

    public function joinBattleground(PlayerCharacter $character): bool
    {
        $this->log("joinBattleground", ['character' => $character->getName()]);
        return $this->realService->joinBattleground($character);
    }

    public function useAuctionHouse(PlayerCharacter $character): bool
    {
        $this->log("useAuctionHouse", ['character' => $character->getName()]);
        return $this->realService->useAuctionHouse($character);
    }

    public function sendMail(PlayerCharacter $from, PlayerCharacter $to, string $subject, string $body, ?Item $item, GoldAmount $cod): bool
    {
        $this->log("sendMail", ['from' => $from->getName(), 'to' => $to->getName(), 'subject' => $subject]);
        return $this->realService->sendMail($from, $to, $subject, $body, $item, $cod);
    }

    private function log(string $method, array $params): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $paramStr = json_encode($params);
        error_log("[{$timestamp}] GameService::{$method}({$paramStr})");
    }
}