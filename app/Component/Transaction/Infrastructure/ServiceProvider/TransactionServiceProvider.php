<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\ServiceProvider;

use App\Component\Transaction\Application\Service\TransactionService;
use App\Component\Transaction\Application\UseCase\TransferMoneyUseCase;
use App\Component\Transaction\Infrastructure\Service\TransactionApplicationService;
use App\Component\Transaction\Infrastructure\ViewQuery\TransactionApplicationViewQuery;
use App\Component\Transaction\Presentation\ViewQuery\TransactionViewQuery;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(TransactionService::class, TransactionApplicationService::class);
        $this->app->singleton(TransactionViewQuery::class, TransactionApplicationViewQuery::class);
        $this->app->singleton(TransferMoneyUseCase::class);
    }

    public function provides(): array
    {
        return [
            TransactionService::class,
            TransactionViewQuery::class,
            TransferMoneyUseCase::class,
        ];
    }
}
