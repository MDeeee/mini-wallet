<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Test\Feature\Http;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTransactionListHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testItListsUserTransactions(): void
    {
        $user = User::factory()->create(['balance' => 1000]);
        $otherUser = User::factory()->create(['balance' => 500]);

        // Create transactions for the user as a sender
        TransactionEntity::factory()->create([
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id,
        ]);

        // Create transactions for the user as a receiver
        TransactionEntity::factory()->create([
            'sender_id' => $otherUser->id,
            'receiver_id' => $user->id,
        ]);

        // Create a transaction not related to the user
        TransactionEntity::factory()->create();

        $this
            ->getJson(route('api.v1.transaction.user-list', $user->id))
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ])
            ->assertJsonCount(2, 'data');
    }
}
