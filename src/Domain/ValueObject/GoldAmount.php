<?php

declare(strict_types=1);

namespace AzerothEngine\Domain\ValueObject;

readonly class GoldAmount
{
    public function __construct(
        public int $copper
    ) {
        if ($copper < 0) {
            throw new \InvalidArgumentException('Gold amount cannot be negative');
        }
    }

    public static function fromGold(int $gold): self
    {
        return new self($gold * 10000);
    }

    public static function fromSilver(int $silver): self
    {
        return new self($silver * 100);
    }

    public static function fromCopper(int $copper): self
    {
        return new self($copper);
    }

    public function getGold(): int
    {
        return (int) floor($this->copper / 10000);
    }

    public function getSilver(): int
    {
        return (int) floor(($this->copper % 10000) / 100);
    }

    public function getCopper(): int
    {
        return $this->copper % 100;
    }

    public function add(GoldAmount $other): self
    {
        return new self($this->copper + $other->copper);
    }

    public function subtract(GoldAmount $other): self
    {
        if ($other->copper > $this->copper) {
            throw new \InvalidArgumentException('Insufficient gold');
        }
        return new self($this->copper - $other->copper);
    }

    public function canAfford(GoldAmount $cost): bool
    {
        return $this->copper >= $cost->copper;
    }

    public function multiply(int $factor): self
    {
        if ($factor < 0) {
            throw new \InvalidArgumentException('Factor cannot be negative');
        }
        return new self($this->copper * $factor);
    }

    public function __toString(): string
    {
        $gold = $this->getGold();
        $silver = $this->getSilver();
        $copper = $this->getCopper();

        $parts = [];
        if ($gold > 0) {
            $parts[] = "{$gold}g";
        }
        if ($silver > 0) {
            $parts[] = "{$silver}s";
        }
        if ($copper > 0 || empty($parts)) {
            $parts[] = "{$copper}c";
        }

        return implode(' ', $parts);
    }
}