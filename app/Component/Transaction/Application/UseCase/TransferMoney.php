<?php

declare(strict_types=1);

namespace App\Component\Transaction\Application\UseCase;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use App\Component\Transaction\Domain\Dto\TransactionDto;
use App\Component\Transaction\Domain\Dto\TransferMoneyDto;
use App\Component\Transaction\Domain\Dto\TransferResultDto;
use App\Component\Transaction\Domain\Exception\InsufficientBalanceException;
use App\Component\Transaction\Domain\Exception\UserNotFoundException;
use App\Component\Transaction\Domain\ValueObject\Money;
use App\Component\Transaction\Infrastructure\Repository\TransactionEloquentRepository;
use App\Events\MoneyTransferred;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class TransferMoney
{
    public function __construct(
        private TransactionEloquentRepository $transactionRepository,
        private ConnectionInterface $connection
    ) {
    }

    /**
     * Execute money transfer with 1.5% commission
     * This operation is atomic - if any step fails, everything rolls back
     *
     * @throws UserNotFoundException
     * @throws InsufficientBalanceException|Throwable
     */
    public function execute(TransferMoneyDto $dto): TransferResultDto
    {
        return $this->connection->transaction(function () use ($dto) {
            // Lock users to prevent race conditions
            $sender = User::where('id', $dto->senderId)->lockForUpdate()->first();
            $receiver = User::where('id', $dto->receiverId)->lockForUpdate()->first();

            // Validate users exist
            if (!$sender) {
                throw UserNotFoundException::withId($dto->senderId);
            }
            if (!$receiver) {
                throw UserNotFoundException::withId($dto->receiverId);
            }

            $senderBalance = Money::fromCents($sender->balance);

            // Validate sufficient balance
            if ($senderBalance->isLessThan($dto->totalDebit)) {
                throw InsufficientBalanceException::forUser(
                    $sender->id,
                    $dto->totalDebit->toFloat(),
                    $senderBalance->toFloat()
                );
            }

            // Update balances
            $newSenderBalance = $senderBalance->subtract($dto->totalDebit);
            $newReceiverBalance = Money::fromCents($receiver->balance)->add($dto->amount);

            $sender->balance = $newSenderBalance->toCents();
            $receiver->balance = $newReceiverBalance->toCents();

            $sender->save();
            $receiver->save();

            // Create a transaction record
            $transactionId = $this->transactionRepository->create(
                new TransactionDto(
                    senderId: $dto->senderId,
                    receiverId: $dto->receiverId,
                    amount: $dto->amount->toCents(),
                    commissionFee: $dto->commission->toCents(),
                    status: 'completed'
                )
            );

            $transaction = TransactionEntity::find($transactionId);

            // Broadcast event to both sender and receiver via Pusher
            event(new MoneyTransferred(
                senderId: $dto->senderId,
                receiverId: $dto->receiverId,
                transactionId: $transactionId,
                amount: $dto->amount->toCents(),
                commissionFee: $dto->commission->toCents(),
                senderNewBalance: $newSenderBalance->toCents(),
                receiverNewBalance: $newReceiverBalance->toCents(),
            ));

            return new TransferResultDto(
                transaction: $transaction,
                senderNewBalance: $newSenderBalance->toFloat()
            );
        });
    }
}
