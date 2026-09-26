<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\ValueObject\GoldAmount;

class InGameGoldPayment implements PaymentMethodInterface
{
    private PlayerCharacter $character;

    public function __construct(PlayerCharacter $character)
    {
        $this->character = $character;
    }

    public function charge(GoldAmount $amount): PaymentResult
    {
        if ($this->character->getGold()->canAfford($amount)) {
            $this->character->spendGold($amount);
            return new PaymentResult(
                true,
                'txn_' . uniqid(),
                "Charged {$amount} from in-game gold"
            );
        }
        return new PaymentResult(
            false,
            '',
            'Insufficient gold'
        );
    }

    public function refund(GoldAmount $amount): PaymentResult
    {
        $this->character->addGold($amount);
        return new PaymentResult(
            true,
            'ref_' . uniqid(),
            "Refunded {$amount} to in-game gold"
        );
    }

    public function getName(): string
    {
        return 'In-Game Gold';
    }
}