<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

/** @var Subscription[] */
class InMemorySubscriptionRepository implements SubscriptionRepositoryInterface
{
    private array $subscriptions = [];

    public function findByAccountId(string $accountId): ?Subscription
    {
        return $this->subscriptions[$accountId] ?? null;
    }

    public function save(Subscription $subscription): void
    {
        $this->subscriptions[$subscription->getAccountId()] = $subscription;
    }
}