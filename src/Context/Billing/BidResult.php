<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

class BidResult
{
    public function __construct(
        public bool $success,
        public string $transactionId,
        public string $message
    ) {}
}