<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Domain\Dto;

use App\Component\Transaction\Domain\ValueObject\Money;

readonly class TransferMoneyDto
{
    public Money $amount;
    public Money $commission;
    public Money $totalDebit;

    public function __construct(
        public int $senderId,
        public int $receiverId,
        float $amountInDollars
    ) {
        $this->amount = Money::fromFloat($amountInDollars);
        $this->commission = $this->amount->calculatePercentage(1.5);
        $this->totalDebit = $this->amount->add($this->commission);
    }
}
