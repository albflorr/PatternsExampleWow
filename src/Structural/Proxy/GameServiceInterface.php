<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Proxy;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

interface GameServiceInterface
{
    public function enterZone(PlayerCharacter $character, string $zoneId): bool;
    public function purchaseItem(PlayerCharacter $character, Item $item): bool;
    public function accessBank(PlayerCharacter $character): array;
    public function joinBattleground(PlayerCharacter $character): bool;
    public function useAuctionHouse(PlayerCharacter $character): bool;
    public function sendMail(PlayerCharacter $from, PlayerCharacter $to, string $subject, string $body, ?Item $item, GoldAmount $cod): bool;
}