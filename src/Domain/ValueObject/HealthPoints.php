<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

readonly class HealthPoints
{
    public function __construct(
        public int $current,
        public int $maximum
    ) {
        if ($maximum <= 0) {
            throw new \InvalidArgumentException('Maximum health must be greater than zero');
        }
        if ($current < 0) {
            throw new \InvalidArgumentException('Current health cannot be negative');
        }
        if ($current > $maximum) {
            throw new \InvalidArgumentException('Current health cannot exceed maximum');
        }
    }

    public static function full(int $maximum): self
    {
        return new self($maximum, $maximum);
    }

    public static function empty(int $maximum): self
    {
        return new self(0, $maximum);
    }

    public function isFull(): bool
    {
        return $this->current === $this->maximum;
    }

    public function isEmpty(): bool
    {
        return $this->current === 0;
    }

    public function getPercentage(): float
    {
        return $this->maximum > 0 ? ($this->current / $this->maximum) * 100.0 : 0.0;
    }

    public function withCurrent(int $newCurrent): self
    {
        $clamped = max(0, min($newCurrent, $this->maximum));
        return new self($clamped, $this->maximum);
    }

    public function withMaximum(int $newMaximum): self
    {
        if ($newMaximum <= 0) {
            throw new \InvalidArgumentException('Maximum health must be greater than zero');
        }
        $clamped = min($this->current, $newMaximum);
        return new self($clamped, $newMaximum);
    }

    public function heal(int $amount): self
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Heal amount cannot be negative');
        }
        return $this->withCurrent($this->current + $amount);
    }

    public function damage(int $amount): self
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Damage amount cannot be negative');
        }
        return $this->withCurrent($this->current - $amount);
    }

    public function __toString(): string
    {
        return "{$this->current}/{$this->maximum}";
    }
}