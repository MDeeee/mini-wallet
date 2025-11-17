<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Domain\Dto;

readonly class TransactionDto
{
    public function __construct(
        public int $senderId,
        public int $receiverId,
        public int $amount,
        public int $commissionFee,
        public string $status
    ) {
    }
}
