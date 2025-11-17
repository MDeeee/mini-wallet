<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Presentation\ViewQuery;

use App\Component\Transaction\Presentation\ViewModel\TransactionViewModel;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

interface TransactionViewQuery
{
    public function getById(int $id): TransactionViewModel;

    public function listAll(int $perPage = 15): CursorPaginator;

    public function listByUserId(int $userId, int $perPage = 15): CursorPaginator;
}
