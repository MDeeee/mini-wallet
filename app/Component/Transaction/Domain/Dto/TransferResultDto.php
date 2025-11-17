<?php

declare(strict_types=1);

namespace App\Component\Transaction\Domain\Dto;

use App\Component\Transaction\Data\Entity\TransactionEntity;

readonly class TransferResultDto
{
    public function __construct(
        public TransactionEntity $transaction,
        public float $senderNewBalance
    ) {
    }
}
