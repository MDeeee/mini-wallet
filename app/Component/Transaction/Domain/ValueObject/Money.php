<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(
        private int $amountInCents
    ) {
        if ($this->amountInCents < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function toCents(): int
    {
        return $this->amountInCents;
    }

    public function toFloat(): float
    {
        return $this->amountInCents / 100;
    }

    public function add(Money $money): self
    {
        return new self($this->amountInCents + $money->amountInCents);
    }

    public function subtract(Money $money): self
    {
        $result = $this->amountInCents - $money->amountInCents;

        if ($result < 0) {
            throw new InvalidArgumentException('Insufficient funds for subtraction');
        }

        return new self($result);
    }

    public function multiply(float $multiplier): self
    {
        return new self((int) round($this->amountInCents * $multiplier));
    }

    public function calculatePercentage(float $percentage): self
    {
        return new self((int) round($this->amountInCents * ($percentage / 100)));
    }

    public function isGreaterThan(Money $money): bool
    {
        return $this->amountInCents > $money->amountInCents;
    }

    public function isGreaterThanOrEqual(Money $money): bool
    {
        return $this->amountInCents >= $money->amountInCents;
    }

    public function isLessThan(Money $money): bool
    {
        return $this->amountInCents < $money->amountInCents;
    }

    public function isEqual(Money $money): bool
    {
        return $this->amountInCents === $money->amountInCents;
    }

    public function __toString(): string
    {
        return number_format($this->toFloat(), 2, '.', '');
    }

    public function toFormattedString(): string
    {
        return '$' . number_format($this->toFloat(), 2, '.', ',');
    }
}
