<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Service;

use App\Component\Transaction\Application\Service\TransactionService;
use App\Component\Transaction\Domain\Dto\TransactionDto;
use App\Component\Transaction\Infrastructure\Repository\TransactionEloquentRepository;

final readonly class TransactionApplicationService implements TransactionService
{
    public function __construct(
        private TransactionEloquentRepository $transactions,
    )
    {
    }

    public function create(TransactionDto $transaction): int
    {
        return $this->transactions->create($transaction);
    }

    public function getById(int $id): TransactionDto
    {
        return $this->transactions->findById($id);
    }

    public function remove(int $id): void
    {
        $this->transactions->remove($id);
    }
}
