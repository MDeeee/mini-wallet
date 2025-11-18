<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Presentation\ViewModel;

use App\Component\Transaction\Domain\ValueObject\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

readonly class TransactionViewModel implements Arrayable
{
    public function __construct(
        public int $id,
        private int $senderId,
        private string $senderName,
        private int $receiverId,
        private string $receiverName,
        private int $amountInCents,
        private int $commissionFeeInCents,
        private string $status,
        public Carbon $created_at
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
            'amount' => Money::fromCents($this->amountInCents)->toArray(),
            'commission_fee' => Money::fromCents($this->commissionFeeInCents)->toArray(),
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
