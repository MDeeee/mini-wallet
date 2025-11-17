<?php

declare(strict_types = 1);

use App\Component\Transaction\Infrastructure\Http\Handler\CreateTransactionHandler;
use App\Component\Transaction\Infrastructure\Http\Handler\GetUserTransactionsHandler;
use App\Component\Transaction\Infrastructure\Http\Handler\RemoveTransactionHandler;
use App\Component\Transaction\Infrastructure\Http\Handler\ShowTransactionHandler;
use App\Component\Transaction\Infrastructure\Http\Handler\TransactionListHandler;
use App\Component\Transaction\Infrastructure\Http\Handler\TransferMoneyHandler;
use App\Component\Transaction\Infrastructure\Http\Handler\UserTransactionListHandler;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->group(function () {
        Route::get('/transactions', GetUserTransactionsHandler::class)
            ->name('transactions.index');

        Route::post('/transactions', TransferMoneyHandler::class)
            ->name('transactions.store');
    });

Route::prefix('transaction')
    ->name('transaction.')
    ->group(function () {
        Route::post('/', CreateTransactionHandler::class)->name('create');
        Route::get('/', TransactionListHandler::class)->name('list');
        Route::get('/{id}', ShowTransactionHandler::class)->name('show')
            ->where('id', '[0-9]+');
        Route::get('/user/{userId}', UserTransactionListHandler::class)->name('user-list')
            ->where('userId', '[0-9]+');
        Route::delete('/{id}', RemoveTransactionHandler::class)->name('remove')
            ->where('id', '[0-9]+');// In real-world scenarios, UUIDs should be used instead of integer IDs
    });
