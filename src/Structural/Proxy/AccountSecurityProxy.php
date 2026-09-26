<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Proxy;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;

class AccountSecurityProxy implements GameServiceInterface
{
    private GameServiceInterface $realService;
    private array $premiumZones = [
        'naxxramas',
        'blackwing_lair',
        'aq40',
        'karazhan',
        'sunwell_plateau',
    ];
    private array $veteranZones = [
        'molten_core',
        'onyxias_lair',
        'zul_gurub',
        'ruins_of_ahn_qiraj',
    ];

    public function __construct(?GameServiceInterface $realService = null)
    {
        $this->realService = $realService ?? new GameService();
    }

    public function enterZone(PlayerCharacter $character, string $zoneId): bool
    {
        $tier = $this->getAccountTier($character);

        if (in_array($zoneId, $this->premiumZones) && !$this->hasAccess($tier, AccountTier::Premium)) {
            echo "[SecurityProxy] ACCESS DENIED: {$character->getName()} (Tier: {$tier->value}) cannot enter premium zone {$zoneId}\n";
            return false;
        }

        if (in_array($zoneId, $this->veteranZones) && !$this->hasAccess($tier, AccountTier::Veteran)) {
            echo "[SecurityProxy] ACCESS DENIED: {$character->getName()} (Tier: {$tier->value}) cannot enter veteran zone {$zoneId}\n";
            return false;
        }

        echo "[SecurityProxy] Access granted for {$character->getName()} to {$zoneId}\n";
        return $this->realService->enterZone($character, $zoneId);
    }

    public function purchaseItem(PlayerCharacter $character, Item $item): bool
    {
        $tier = $this->getAccountTier($character);

        if ($item->quality->value === 'legendary' && !$this->hasAccess($tier, AccountTier::Premium)) {
            echo "[SecurityProxy] PURCHASE DENIED: Free accounts cannot purchase legendary items\n";
            return false;
        }

        if ($item->level->value > 50 && !$this->hasAccess($tier, AccountTier::Veteran)) {
            echo "[SecurityProxy] PURCHASE DENIED: Free accounts cannot purchase items above level 50\n";
            return false;
        }

        return $this->realService->purchaseItem($character, $item);
    }

    public function accessBank(PlayerCharacter $character): array
    {
        $tier = $this->getAccountTier($character);

        if ($tier === AccountTier::Free) {
            echo "[SecurityProxy] Bank access limited for free accounts (5 slots only)\n";
            return array_slice($this->realService->accessBank($character), 0, 5);
        }

        return $this->realService->accessBank($character);
    }

    public function joinBattleground(PlayerCharacter $character): bool
    {
        $tier = $this->getAccountTier($character);

        if ($tier === AccountTier::Free && $character->getLevel() < 10) {
            echo "[SecurityProxy] Free accounts must be level 10+ to join battlegrounds\n";
            return false;
        }

        return $this->realService->joinBattleground($character);
    }

    public function useAuctionHouse(PlayerCharacter $character): bool
    {
        $tier = $this->getAccountTier($character);

        if ($tier === AccountTier::Free) {
            echo "[SecurityProxy] Free accounts have limited auction house access (2 auctions max)\n";
        }

        return $this->realService->useAuctionHouse($character);
    }

    public function sendMail(PlayerCharacter $from, PlayerCharacter $to, string $subject, string $body, ?Item $item, GoldAmount $cod): bool
    {
        $tier = $this->getAccountTier($from);

        if ($tier === AccountTier::Free && $cod->copper > 0) {
            echo "[SecurityProxy] Free accounts cannot send COD mail\n";
            return false;
        }

        if ($tier === AccountTier::Free && $item !== null && $item->quality->value === 'epic') {
            echo "[SecurityProxy] Free accounts cannot mail epic items\n";
            return false;
        }

        return $this->realService->sendMail($from, $to, $subject, $body, $item, $cod);
    }

    private function getAccountTier(PlayerCharacter $character): AccountTier
    {
        if ($character->getLevel() >= 60) {
            return AccountTier::Veteran;
        }
        if ($character->getGold()->copper >= 1000000) {
            return AccountTier::Premium;
        }
        return AccountTier::Free;
    }

    private function hasAccess(AccountTier $current, AccountTier $required): bool
    {
        $hierarchy = [
            AccountTier::Free->value => 0,
            AccountTier::Premium->value => 1,
            AccountTier::Veteran->value => 2,
            AccountTier::GM->value => 3,
        ];
        return $hierarchy[$current->value] >= $hierarchy[$required->value];
    }
}