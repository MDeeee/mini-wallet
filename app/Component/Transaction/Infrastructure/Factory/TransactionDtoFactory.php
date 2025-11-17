<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Factory;

use App\Component\Transaction\Domain\Dto\Contract\Transaction;
use App\Component\Transaction\Domain\Dto\TransactionDto;

final class TransactionDtoFactory
{
    public function createByContract(Transaction $transaction): TransactionDto
    {
        return new TransactionDto(
            senderId: $transaction->senderId(),
            receiverId: $transaction->receiverId(),
            amount: $transaction->amount(),
            commissionFee: $transaction->commissionFee(),
            status: $transaction->status()
        );
    }
}
