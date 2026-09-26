<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Billing;

use AzerothEngine\Domain\ValueObject\GoldAmount;

interface PaymentMethodInterface
{
    public function charge(GoldAmount $amount): PaymentResult;
    public function refund(GoldAmount $amount): PaymentResult;
    public function getName(): string;
}