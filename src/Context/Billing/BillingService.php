<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

use AzerothEngine\Domain\ValueObject\GoldAmount;

class BillingService
{
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $repository)
    {
        $this->subscriptionRepository = $repository;
    }

    public function purchaseSubscription(string $accountId, string $tier, int $months, PaymentMethodInterface $paymentMethod): PaymentResult
    {
        $price = match ($tier) {
            'premium' => GoldAmount::fromGold(15 * $months),
            'veteran' => GoldAmount::fromGold(30 * $months),
            default => GoldAmount::fromGold(0),
        };

        $result = $paymentMethod->charge($price);
        if (!$result->success) {
            return $result;
        }

        $startDate = new \DateTimeImmutable();
        $endDate = $startDate->modify("+{$months} months");

        $subscription = new Subscription(
            accountId: $accountId,
            tier: $tier,
            startDate: $startDate,
            endDate: $endDate,
            autoRenew: true,
            paymentMethodId: $paymentMethod->getName()
        );

        $this->subscriptionRepository->save($subscription);

        return new PaymentResult(
            true,
            $result->transactionId,
            "Subscription {$tier} activated for {$months} month(s)"
        );
    }

    public function cancelSubscription(string $accountId): bool
    {
        $subscription = $this->subscriptionRepository->findByAccountId($accountId);
        if ($subscription === null) {
            return false;
        }

        $subscription->cancel();
        $this->subscriptionRepository->save($subscription);
        return true;
    }

    public function getSubscriptionStatus(string $accountId): array
    {
        $subscription = $this->subscriptionRepository->findByAccountId($accountId);
        if ($subscription === null) {
            return ['active' => false, 'tier' => 'free'];
        }

        return [
            'active' => $subscription->isActive(),
            'tier' => $subscription->getTier(),
            'days_remaining' => $subscription->getDaysRemaining(),
            'auto_renew' => $subscription->isAutoRenew(),
        ];
    }

    public function processRenewals(PaymentMethodInterface $paymentMethod): array
    {
        $results = [];
        foreach ($this->subscriptionRepository as $subscription) {
            if ($subscription->isAutoRenew() && !$subscription->isActive()) {
                $result = $this->purchaseSubscription(
                    $subscription->getAccountId(),
                    $subscription->getTier(),
                    1,
                    $paymentMethod
                );
                $results[] = ['account' => $subscription->getAccountId(), 'result' => $result];
            }
        }
        return $results;
    }
}