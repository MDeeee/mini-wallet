<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Test\Feature\Http;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTransactionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testItShowsTransaction(): void
    {
        $transaction = TransactionEntity::factory()->create();

        $this
            ->getJson(route('api.v1.transaction.show', $transaction->getKey()))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'sender' => ['id', 'name'],
                'receiver' => ['id', 'name'],
                'amount',
                'commission_fee',
                'status',
                'created_at',
            ]);
    }
}
