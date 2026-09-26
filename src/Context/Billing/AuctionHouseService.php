<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

class AuctionHouseService
{
    /** @var AuctionListing[] */
    private array $listings = [];
    private float $commissionRate = 0.05;

    public function createListing(PlayerCharacter $seller, Item $item, GoldAmount $startingBid, GoldAmount $buyoutPrice, int $durationHours = 24): AuctionListing
    {
        $deposit = GoldAmount::fromCopper((int) ($item->sellPrice->copper * 0.05 * $durationHours / 24));
        if (!$seller->getGold()->canAfford($deposit)) {
            throw new \InvalidArgumentException('Cannot afford deposit');
        }

        $seller->spendGold($deposit);

        $listing = new AuctionListing(
            id: 'auc_' . uniqid(),
            sellerId: $seller->getId(),
            sellerName: $seller->getName(),
            item: $item,
            startingBid: $startingBid,
            currentBid: $startingBid,
            buyoutPrice: $buyoutPrice,
            startTime: new \DateTimeImmutable(),
            endTime: (new \DateTimeImmutable())->modify("+{$durationHours} hours"),
            deposit: $deposit
        );

        $this->listings[$listing->id] = $listing;
        return $listing;
    }

    public function placeBid(string $listingId, PlayerCharacter $bidder, GoldAmount $bidAmount): BidResult
    {
        $listing = $this->listings[$listingId] ?? null;
        if ($listing === null) {
            return new BidResult(false, '', 'Listing not found');
        }

        if ($listing->endTime < new \DateTimeImmutable()) {
            return new BidResult(false, '', 'Auction has ended');
        }

        if ($bidAmount->copper <= $listing->currentBid->copper) {
            return new BidResult(false, '', 'Bid must be higher than current bid');
        }

        if (!$bidder->getGold()->canAfford($bidAmount)) {
            return new BidResult(false, '', 'Insufficient gold');
        }

        if ($listing->sellerId === $bidder->getId()) {
            return new BidResult(false, '', 'Cannot bid on own auction');
        }

        if ($listing->currentBidderId !== null) {
        }

        $bidder->spendGold($bidAmount);
        $listing->currentBid = $bidAmount;
        $listing->currentBidderId = $bidder->getId();
        $listing->currentBidderName = $bidder->getName();

        return new BidResult(true, 'bid_' . uniqid(), 'Bid placed successfully');
    }

    public function buyout(string $listingId, PlayerCharacter $buyer): BidResult
    {
        $listing = $this->listings[$listingId] ?? null;
        if ($listing === null) {
            return new BidResult(false, '', 'Listing not found');
        }

        if ($listing->endTime < new \DateTimeImmutable()) {
            return new BidResult(false, '', 'Auction has ended');
        }

        if ($listing->buyoutPrice === null) {
            return new BidResult(false, '', 'No buyout price set');
        }

        if (!$buyer->getGold()->canAfford($listing->buyoutPrice)) {
            return new BidResult(false, '', 'Insufficient gold');
        }

        if ($listing->sellerId === $buyer->getId()) {
            return new BidResult(false, '', 'Cannot buyout own auction');
        }

        $buyer->spendGold($listing->buyoutPrice);
        $this->finalizeSale($listing, $buyer, $listing->buyoutPrice);

        return new BidResult(true, 'buyout_' . uniqid(), 'Buyout successful');
    }

    public function getActiveListings(): array
    {
        $now = new \DateTimeImmutable();
        return array_filter($this->listings, fn($l) => $l->endTime > $now);
    }

    public function getListing(string $listingId): ?AuctionListing
    {
        return $this->listings[$listingId] ?? null;
    }

    private function finalizeSale(AuctionListing $listing, PlayerCharacter $buyer, GoldAmount $finalPrice): void
    {
        $commission = GoldAmount::fromCopper((int) ($finalPrice->copper * $this->commissionRate));
        $sellerPayout = $finalPrice->subtract($commission);

        unset($this->listings[$listing->id]);
    }
}