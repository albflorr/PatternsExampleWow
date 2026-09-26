<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

readonly class ItemId
{
    public function __construct(
        public string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Item ID cannot be empty');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}