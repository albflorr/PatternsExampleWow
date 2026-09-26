<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

class Subscription
{
    private string $accountId;
    private string $tier;
    private \DateTimeImmutable $startDate;
    private \DateTimeImmutable $endDate;
    private bool $autoRenew;
    private string $paymentMethodId;

    public function __construct(
        string $accountId,
        string $tier,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        bool $autoRenew = true,
        string $paymentMethodId = ''
    ) {
        $this->accountId = $accountId;
        $this->tier = $tier;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->autoRenew = $autoRenew;
        $this->paymentMethodId = $paymentMethodId;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function getTier(): string
    {
        return $this->tier;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isAutoRenew(): bool
    {
        return $this->autoRenew;
    }

    public function isActive(): bool
    {
        return new \DateTimeImmutable() <= $this->endDate;
    }

    public function getDaysRemaining(): int
    {
        $now = new \DateTimeImmutable();
        if ($now > $this->endDate) {
            return 0;
        }
        return $now->diff($this->endDate)->days;
    }

    public function renew(int $months = 1): void
    {
        $this->endDate = $this->endDate->modify("+{$months} months");
    }

    public function cancel(): void
    {
        $this->autoRenew = false;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }
}