<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $transactionId,
        public string $message
    ) {}
}