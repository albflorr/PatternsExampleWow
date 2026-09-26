<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Proxy;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

class GameService implements GameServiceInterface
{
    public function enterZone(PlayerCharacter $character, string $zoneId): bool
    {
        echo "[GameService] {$character->getName()} entered zone: {$zoneId}\n";
        return true;
    }

    public function purchaseItem(PlayerCharacter $character, Item $item): bool
    {
        if ($character->getGold()->canAfford($item->buyPrice)) {
            $character->spendGold($item->buyPrice);
            echo "[GameService] {$character->getName()} purchased {$item->name} for {$item->buyPrice}\n";
            return true;
        }
        echo "[GameService] {$character->getName()} cannot afford {$item->name}\n";
        return false;
    }

    public function accessBank(PlayerCharacter $character): array
    {
        echo "[GameService] {$character->getName()} accessed bank\n";
        return [];
    }

    public function joinBattleground(PlayerCharacter $character): bool
    {
        echo "[GameService] {$character->getName()} joined battleground\n";
        return true;
    }

    public function useAuctionHouse(PlayerCharacter $character): bool
    {
        echo "[GameService] {$character->getName()} accessed auction house\n";
        return true;
    }

    public function sendMail(PlayerCharacter $from, PlayerCharacter $to, string $subject, string $body, ?Item $item, GoldAmount $cod): bool
    {
        echo "[GameService] {$from->getName()} sent mail to {$to->getName()}: {$subject}\n";
        return true;
    }
}