<?php

declare(strict_types=1);

namespace App\Component\Transaction\Presentation\ViewModel;

use App\Component\Transaction\Domain\ValueObject\Money;
use Illuminate\Contracts\Support\Arrayable;

readonly class TransferResultViewModel implements Arrayable
{
    public function __construct(
        private int $transactionId,
        private int $senderId,
        private int $receiverId,
        private Money $amount,
        private Money $commissionFee,
        private Money $senderNewBalance,
        private string $message
    ) {
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'transaction' => [
                'id' => $this->transactionId,
                'sender_id' => $this->senderId,
                'receiver_id' => $this->receiverId,
                'amount' => $this->amount->toArray(),
                'commission_fee' => $this->commissionFee->toArray(),
            ],
            'new_balance' => $this->senderNewBalance->toArray(),
        ];
    }
}
