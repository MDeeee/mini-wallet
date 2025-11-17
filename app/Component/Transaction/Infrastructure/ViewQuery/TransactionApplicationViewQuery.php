<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\ViewQuery;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use App\Component\Transaction\Presentation\ViewModel\TransactionViewModel;
use App\Component\Transaction\Presentation\ViewQuery\TransactionViewQuery;
use Illuminate\Contracts\Pagination\CursorPaginator;

final class TransactionApplicationViewQuery implements TransactionViewQuery
{
    public function getById(int $id): TransactionViewModel
    {
        $record = TransactionEntity::query()
            ->with(['sender:id,name', 'receiver:id,name'])
            ->findOrFail($id);

        return new TransactionViewModel(
            id: $record->getKey(),
            senderId: $record->sender->id,
            senderName: $record->sender->name,
            receiverId: $record->receiver->id,
            receiverName: $record->receiver->name,
            amountInCents: (int) $record->amount,
            commissionFeeInCents: (int) $record->commission_fee,
            status: $record->status->value,
            createdAt: $record->created_at,
        );
    }

    public function listAll(int $perPage = 15): CursorPaginator
    {
        return TransactionEntity::query()
            ->with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage)
            ->through(fn (TransactionEntity $record) => new TransactionViewModel(
                id: $record->getKey(),
                senderId: $record->sender->id,
                senderName: $record->sender->name,
                receiverId: $record->receiver->id,
                receiverName: $record->receiver->name,
                amountInCents: (int) $record->amount,
                commissionFeeInCents: (int) $record->commission_fee,
                status: $record->status->value,
                createdAt: $record->created_at,
            ));
    }

    public function listByUserId(int $userId, int $perPage = 15): CursorPaginator
    {
        return TransactionEntity::query()
            ->with(['sender:id,name', 'receiver:id,name'])
            ->where(fn ($query) => $query
                ->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage)
            ->through(fn (TransactionEntity $record) => new TransactionViewModel(
                id: $record->getKey(),
                senderId: $record->sender->id,
                senderName: $record->sender->name,
                receiverId: $record->receiver->id,
                receiverName: $record->receiver->name,
                amountInCents: (int) $record->amount,
                commissionFeeInCents: (int) $record->commission_fee,
                status: $record->status->value,
                createdAt: $record->created_at,
            ));
    }
}
