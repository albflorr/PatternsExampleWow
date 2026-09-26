<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

interface SubscriptionRepositoryInterface
{
    public function findByAccountId(string $accountId): ?Subscription;
    public function save(Subscription $subscription): void;
}