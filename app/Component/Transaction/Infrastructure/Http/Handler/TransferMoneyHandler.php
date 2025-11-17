<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Http\Handler;

use App\Component\Transaction\Application\UseCase\TransferMoney;
use App\Component\Transaction\Domain\Dto\TransferMoneyDto;
use App\Component\Transaction\Domain\Exception\InsufficientBalanceException;
use App\Component\Transaction\Domain\Exception\UserNotFoundException;
use App\Component\Transaction\Domain\ValueObject\Money;
use App\Component\Transaction\Infrastructure\Http\Request\TransferMoneyRequest;
use App\Component\Transaction\Presentation\ViewModel\TransferResultViewModel;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class TransferMoneyHandler
{
    public function __construct(
        private TransferMoney $transferMoney,
        private ResponseFactory $response
    ) {
    }

    /**
     * @throws UserNotFoundException
     * @throws Throwable
     * @throws InsufficientBalanceException
     */
    public function __invoke(TransferMoneyRequest $request): Response
    {
        $dto = new TransferMoneyDto(
            senderId: $request->senderId(),
            receiverId: $request->receiverId(),
            amountInDollars: $request->amount()
        );

        $result = $this->transferMoney->execute($dto);

        return $this->response->json(
            new TransferResultViewModel(
                transactionId: $result->transaction->id,
                senderId: $result->transaction->sender_id,
                receiverId: $result->transaction->receiver_id,
                amount: Money::fromCents($result->transaction->amount)->toFloat(),
                commissionFee: Money::fromCents($result->transaction->commission_fee)->toFloat(),
                senderNewBalance: $result->senderNewBalance,
                message: 'Transfer completed successfully'
            ),
            201
        );
    }
}
