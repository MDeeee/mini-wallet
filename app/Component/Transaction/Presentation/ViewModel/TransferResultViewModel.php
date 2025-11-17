<?php

declare(strict_types=1);

namespace App\Component\Transaction\Presentation\ViewModel;

use Illuminate\Contracts\Support\Arrayable;

readonly class TransferResultViewModel implements Arrayable
{
    public function __construct(
        private int $transactionId,
        private int $senderId,
        private int $receiverId,
        private float $amount,
        private float $commissionFee,
        private float $senderNewBalance,
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
                'amount' => $this->amount,
                'commission_fee' => $this->commissionFee,
            ],
            'new_balance' => $this->senderNewBalance,
        ];
    }
}
