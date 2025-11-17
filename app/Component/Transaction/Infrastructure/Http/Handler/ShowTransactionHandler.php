<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Http\Handler;

use App\Component\Transaction\Presentation\ViewQuery\TransactionViewQuery;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

final readonly class ShowTransactionHandler
{
    public function __construct(
        private TransactionViewQuery $viewQuery,
        private ResponseFactory $response
    ) {
    }

    public function __invoke(int $id): Response
    {
        return $this->response->json(
            $this->viewQuery->getById($id)
        );
    }
}
