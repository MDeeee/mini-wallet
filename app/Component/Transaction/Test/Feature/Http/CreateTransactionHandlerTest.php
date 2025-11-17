<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Test\Feature\Http;

use App\Component\Transaction\Domain\Enum\TransactionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTransactionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testOkResponse(): void
    {
        $sender = User::factory()->create(['balance' => 1000]);
        $receiver = User::factory()->create(['balance' => 500]);

        $transactionId = $this
            ->postJson(route('api.v1.transaction.create'), [
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => 100.00,
                'commission_fee' => 1.00,
                'status' => TransactionStatus::COMPLETED->value,
            ])
            ->assertOk()
            ->json('id');

        $this
            ->assertDatabaseHas('transactions', [
                'id' => $transactionId,
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => 10000, // $100 in cents
                'commission_fee' => 100, // $1 in cents
                'status' => TransactionStatus::COMPLETED->value,
            ]);
    }
}
