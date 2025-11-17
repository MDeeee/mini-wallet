<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Test\Feature\Http;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionListHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testItListsAllTransactions(): void
    {
        TransactionEntity::factory()->count(3)->create();

        $this
            ->getJson(route('api.v1.transaction.list'))
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ])
            ->assertJsonCount(3, 'data');
    }
}
