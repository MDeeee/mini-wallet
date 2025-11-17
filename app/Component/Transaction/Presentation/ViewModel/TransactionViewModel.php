<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Presentation\ViewModel;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

readonly class TransactionViewModel implements Arrayable
{
    public function __construct(
        private int $id,
        private int $senderId,
        private string $senderName,
        private int $receiverId,
        private string $receiverName,
        private int $amountInCents,
        private int $commissionFeeInCents,
        private string $status,
        private Carbon $createdAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sender' => [
                'id' => $this->senderId,
                'name' => $this->senderName,
            ],
            'receiver' => [
                'id' => $this->receiverId,
                'name' => $this->receiverName,
            ],
            'amount' => \App\Component\Transaction\Domain\ValueObject\Money::fromCents($this->amountInCents)->toFormattedString(),
            'amount_cents' => $this->amountInCents,
            'commission_fee' => \App\Component\Transaction\Domain\ValueObject\Money::fromCents($this->commissionFeeInCents)->toFormattedString(),
            'commission_fee_cents' => $this->commissionFeeInCents,
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
