<?php

declare(strict_types=1);

namespace App\Component\Transaction\Test\Feature\Api;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use App\Component\Transaction\Domain\ValueObject\Money;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_their_transaction_history_and_balance(): void
    {
        $user = User::factory()->create(['balance' => 50000]); // $500 in cents
        $otherUser = User::factory()->create(['balance' => 30000]); // $300 in cents

        // Create some transactions
        TransactionEntity::factory()->create([
            'sender_id' => $user->id,
            'receiver_id' => $otherUser->id,
            'amount' => 10000, // $100 in cents
        ]);

        $response = $this->actingAs($user)->getJson(route('api.v1.transactions.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'transactions',
                'balance' => ['amount', 'amount_cents', 'currency'],
            ])
            ->assertJson([
                'balance' => [
                    'amount' => '500.00',
                    'amount_cents' => 50000,
                    'currency' => 'USD',
                ],
            ]);
    }

    public function test_user_can_transfer_money_successfully(): void
    {
        $sender = User::factory()->create(['balance' => 20000]); // $200 in cents
        $receiver = User::factory()->create(['balance' => 10000]); // $100 in cents

        $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 100.00,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'transaction' => [
                    'id',
                    'sender_id',
                    'receiver_id',
                    'amount' => ['amount', 'amount_cents', 'currency'],
                    'commission_fee' => ['amount', 'amount_cents', 'currency']
                ],
                'new_balance' => ['amount', 'amount_cents', 'currency'],
            ])
            ->assertJson([
                'message' => 'Transfer completed successfully',
                'transaction' => [
                    'amount' => [
                        'amount' => '100.00',
                        'amount_cents' => 10000,
                        'currency' => 'USD',
                    ],
                    'commission_fee' => [
                        'amount' => '1.50',
                        'amount_cents' => 150,
                        'currency' => 'USD',
                    ],
                ],
            ]);

        // Verify balances updated correctly
        $sender->refresh();
        $receiver->refresh();

        $this->assertEquals(9850, $sender->balance); // 20000 - 10000 - 150 (cents)
        $this->assertEquals(20000, $receiver->balance); // 10000 + 10000 (cents)
    }

    public function test_validation_fails_when_receiver_does_not_exist(): void
    {
        $sender = User::factory()->create(['balance' => 20000]); // $200 in cents

        $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => 99999,
            'amount' => 100.00,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['receiver_id']);
    }

    public function test_validation_fails_when_trying_to_send_to_self(): void
    {
        $user = User::factory()->create(['balance' => 20000]); // $200 in cents

        $response = $this->actingAs($user)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $user->id,
            'amount' => 100.00,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['receiver_id']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidAmountProvider')]
    public function test_validation_fails_with_invalid_amount($amount): void
    {
        $sender = User::factory()->create(['balance' => 20000]); // $200 in cents
        $receiver = User::factory()->create(['balance' => 10000]); // $100 in cents

        $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => $amount,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public static function invalidAmountProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-10],
            'too_small' => [0.001],
            'too_large' => [2000000],
            'invalid_string' => ['invalid'],
        ];
    }

    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $sender = User::factory()->create(['balance' => 5000]); // $50 in cents
        $receiver = User::factory()->create(['balance' => 10000]); // $100 in cents

        $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => "Insufficient balance for user {$sender->id}. Required: 101.5, Available: 50"
            ]);

        // Verify no balance changes occurred
        $sender->refresh();
        $receiver->refresh();

        $this->assertEquals(5000, $sender->balance); // $50 in cents
        $this->assertEquals(10000, $receiver->balance); // $100 in cents
    }

    public function test_unauthenticated_user_cannot_access_transactions(): void
    {
        $this->getJson(route('api.v1.transactions.index'))->assertUnauthorized();
        $this->postJson(route('api.v1.transactions.store'))->assertUnauthorized();
    }

    public function test_rate_limiting_prevents_too_many_transfer_requests(): void
    {
        $sender = User::factory()->create(['balance' => 1000000]); // $10,000 in cents
        $receiver = User::factory()->create(['balance' => 10000]); // $100 in cents

        // Make 61 requests (limit is 60 per minute)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => 1.00,
            ]);
        }

        // The 61st request should be rate limited
        $response->assertStatus(429); // Too Many Requests
    }

    public function test_commission_calculation_is_correct(): void
    {
        $sender = User::factory()->create(['balance' => 100000]); // $1000 in cents
        $receiver = User::factory()->create(['balance' => 0]);

        // Test various amounts
        $testCases = [
            ['amount' => 100.00, 'commission' => 1.50, 'total_debit' => 101.50],
            ['amount' => 50.00, 'commission' => 0.75, 'total_debit' => 50.75],
            ['amount' => 200.00, 'commission' => 3.00, 'total_debit' => 203.00],
        ];

        foreach ($testCases as $case) {
            $sender->update(['balance' => 100000]); // $1000 in cents
            $receiver->update(['balance' => 0]);

            $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => $case['amount'],
            ]);

            $response->assertCreated()
                ->assertJson([
                    'transaction' => [
                        'amount' => [
                            'amount' => number_format($case['amount'], 2, '.', ''),
                            'amount_cents' => (int)($case['amount'] * 100),
                            'currency' => 'USD',
                        ],
                        'commission_fee' => [
                            'amount' => number_format($case['commission'], 2, '.', ''),
                            'amount_cents' => (int)($case['commission'] * 100),
                            'currency' => 'USD',
                        ],
                    ],
                ]);

            $sender->refresh();
            $expectedBalance = Money::fromFloat(1000)->subtract(Money::fromFloat($case['total_debit']));
            $this->assertEquals(
                $expectedBalance->toCents(),
                $sender->balance,
                "Sender balance incorrect for amount {$case['amount']}"
            );
        }
    }
}
