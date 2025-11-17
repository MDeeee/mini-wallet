<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Http\Handler;

use App\Component\Transaction\Application\Service\TransactionService;
use App\Component\Transaction\Infrastructure\Factory\TransactionDtoFactory;
use App\Component\Transaction\Infrastructure\Http\Request\TransactionRequest;
use App\Component\Transaction\Presentation\ViewQuery\TransactionViewQuery;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\ConnectionInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class CreateTransactionHandler
{
    public function __construct(
        private TransactionService $service,
        private TransactionDtoFactory $dto,
        private TransactionViewQuery $viewQuery,
        private ResponseFactory $response,
        private ConnectionInterface $connection,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(TransactionRequest $request): Response
    {
        $id = $this->connection->transaction(fn () => $this->service->create(
            $this->dto->createByContract($request)
        ));

        return $this->response->json(
            $this->viewQuery->getById($id)
        );
    }
}
