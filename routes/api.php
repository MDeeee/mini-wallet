<?php

declare(strict_types=1);

use App\Component\Transaction\Domain\ValueObject\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])
    ->get('/user', function (Request $request) {
        $user = $request->user();
        $balanceMoney = Money::fromCents($user->balance);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'balance' => $balanceMoney->toArray(),
        ]);
    })->name('user.current');
