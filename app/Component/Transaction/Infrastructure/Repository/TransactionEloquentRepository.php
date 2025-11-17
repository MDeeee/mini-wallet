<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Repository;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use App\Component\Transaction\Domain\Dto\TransactionDto;
use App\Component\Transaction\Domain\Exception\TransactionNotFoundException;

final class TransactionEloquentRepository
{
    public function create(TransactionDto $transaction): int
    {
        $record = TransactionEntity::create([
            'sender_id' => $transaction->senderId,
            'receiver_id' => $transaction->receiverId,
            'amount' => $transaction->amount,
            'commission_fee' => $transaction->commissionFee,
            'status' => $transaction->status,
            'created_at' => now(),
        ]);

        return $record->getKey();
    }

    /**
     * @throws TransactionNotFoundException
     */
    public function findById(int $id): TransactionDto
    {
        $record = TransactionEntity::find($id);

        if (!$record) {
            throw TransactionNotFoundException::withId($id);
        }

        return new TransactionDto(
            senderId: $record->sender_id,
            receiverId: $record->receiver_id,
            amount: $record->amount,
            commissionFee: $record->commission_fee,
            status: $record->status
        );
    }

    public function remove(int $id): void
    {
        TransactionEntity::findOrFail($id)->delete();
    }
}
