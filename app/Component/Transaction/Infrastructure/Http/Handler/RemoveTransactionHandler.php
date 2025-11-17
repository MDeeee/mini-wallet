<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Http\Handler;

use App\Component\Transaction\Application\Service\TransactionService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\ConnectionInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class RemoveTransactionHandler
{
    public function __construct(
        private TransactionService $service,
        private ResponseFactory $response,
        private ConnectionInterface $connection
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(int $id): Response
    {
        $this->connection->transaction(fn () => $this->service->remove($id));

        return $this->response->noContent();
    }
}
