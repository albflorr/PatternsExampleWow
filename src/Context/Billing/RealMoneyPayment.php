<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

use AzerothEngine\Domain\ValueObject\GoldAmount;

class RealMoneyPayment implements PaymentMethodInterface
{
    private string $accountId;
    private float $balance;

    public function __construct(string $accountId, float $initialBalance = 0.0)
    {
        $this->accountId = $accountId;
        $this->balance = $initialBalance;
    }

    public function charge(GoldAmount $amount): PaymentResult
    {
        $realMoneyAmount = $amount->copper / 10000;

        if ($this->balance >= $realMoneyAmount) {
            $this->balance -= $realMoneyAmount;
            return new PaymentResult(
                true,
                'txn_' . uniqid(),
                "Charged \${$realMoneyAmount} from real money balance"
            );
        }
        return new PaymentResult(
            false,
            '',
            'Insufficient real money balance'
        );
    }

    public function refund(GoldAmount $amount): PaymentResult
    {
        $realMoneyAmount = $amount->copper / 10000;
        $this->balance += $realMoneyAmount;
        return new PaymentResult(
            true,
            'ref_' . uniqid(),
            "Refunded \${$realMoneyAmount} to real money balance"
        );
    }

    public function getName(): string
    {
        return 'Real Money';
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function addFunds(float $amount): void
    {
        $this->balance += $amount;
    }
}