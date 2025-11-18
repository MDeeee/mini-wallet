<?php

declare(strict_types=1);

namespace App\Component\Transaction\Infrastructure\Http\Handler;

use App\Component\Transaction\Domain\ValueObject\Money;
use App\Component\Transaction\Presentation\ViewQuery\TransactionViewQuery;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class GetUserTransactionsHandler
{
    public function __construct(
        private TransactionViewQuery $viewQuery,
        private ResponseFactory $response
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $balanceMoney = Money::fromCents(auth()->user()?->balance ?? 0);

        return $this->response->json([
            'transactions' => $this->viewQuery->listByUserId(auth()->id(), perPage: 15),
            'balance' => $balanceMoney->toArray(),
        ]);
    }
}
