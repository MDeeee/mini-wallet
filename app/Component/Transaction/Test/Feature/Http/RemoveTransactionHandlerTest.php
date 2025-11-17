<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Test\Feature\Http;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveTransactionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testRemovesTransaction(): void
    {
        $transaction = TransactionEntity::factory()->create();

        $this
            ->deleteJson(route('api.v1.transaction.remove', $transaction->getKey()))
            ->assertNoContent();

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->getKey(),
        ]);
    }
}
