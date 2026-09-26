<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\GoldAmount;

class AuctionListing
{
    public function __construct(
        public string $id,
        public string $sellerId,
        public string $sellerName,
        public Item $item,
        public GoldAmount $startingBid,
        public GoldAmount $currentBid,
        public ?GoldAmount $buyoutPrice,
        public \DateTimeImmutable $startTime,
        public \DateTimeImmutable $endTime,
        public GoldAmount $deposit,
        public ?string $currentBidderId = null,
        public ?string $currentBidderName = null
    ) {}
}