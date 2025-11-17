<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Application\Service;

use App\Component\Transaction\Domain\Dto\TransactionDto;

interface TransactionService
{
    public function create(TransactionDto $transaction): int;
    public function getById(int $id): TransactionDto;
    public function remove(int $id): void;
}
