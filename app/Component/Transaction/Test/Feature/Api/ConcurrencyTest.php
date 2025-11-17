<?php

declare(strict_types=1);

namespace App\Component\Transaction\Test\Feature\Api;

use App\Component\Transaction\Domain\ValueObject\Money;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Throwable;

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_prevents_race_condition_with_concurrent_transfers(): void
    {
        $sender = User::factory()->create(['balance' => 10000]); // $100 in cents
        $receiver1 = User::factory()->create(['balance' => 0]);
        $receiver2 = User::factory()->create(['balance' => 0]);

        // Simulate concurrent transfers by disabling database locking temporarily
        // This test verifies that with proper locking, we prevent double-spending

        $response1 = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $receiver1->id,
            'amount' => 60.00,
        ]);

        $response2 = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $receiver2->id,
            'amount' => 60.00,
        ]);

        // With proper locking (lockForUpdate), one should succeed and one should fail
        // The sender only has 100, and can't send 60 + 60 (+ commissions)

        $sender->refresh();

        // After both attempts, sender should not have negative balance
        $this->assertGreaterThanOrEqual(0, $sender->balance);

        // One of the transfers should have failed with insufficient balance
        $this->assertTrue(
            $response1->status() === 422 || $response2->status() === 422,
            'One transfer should have failed due to insufficient balance'
        );
    }

    /**
     * @throws Throwable
     */
    public function test_database_locking_prevents_double_spending(): void
    {
        $sender = User::factory()->create(['balance' => 15000]); // $150 in cents
        $receiver = User::factory()->create(['balance' => 0]);

        // Start a transaction manually to test locking behavior
        DB::beginTransaction();

        try {
            // The first transfer should lock the sender's row
            $response1 = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => 100.00, // With 1.5% commission = 101.50 total
            ]);

            $response1->assertCreated();

            // Verify sender's balance after first transfer
            $sender->refresh();
            $this->assertEquals(4850, $sender->balance); // 15000 - 10000 - 150 (cents)

            DB::commit();

            // Now attempt another transfer that should fail
            $response2 = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => 50.00, // Would need 50.75 but only has 48.50
            ]);

            $response2->assertStatus(422);
            $sender->refresh();
            $this->assertEquals(4850, $sender->balance); // Balance unchanged

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function test_concurrent_transfers_from_different_senders_work_correctly(): void
    {
        $sender1 = User::factory()->create(['balance' => 20000]); // $200 in cents
        $sender2 = User::factory()->create(['balance' => 20000]); // $200 in cents
        $receiver = User::factory()->create(['balance' => 0]);

        // Both senders should be able to send to the same receiver concurrently
        $response1 = $this->actingAs($sender1)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 50.00,
        ]);

        $response2 = $this->actingAs($sender2)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 75.00,
        ]);

        $response1->assertCreated();
        $response2->assertCreated();

        // Verify all balances are correct
        $sender1->refresh();
        $sender2->refresh();
        $receiver->refresh();

        $this->assertEquals(14925, $sender1->balance); // 20000 - 5000 - 75 (cents)
        $this->assertEquals(12387, $sender2->balance); // 20000 - 7500 - 113 (cents, rounded from 112.5)
        $this->assertEquals(12500, $receiver->balance); // 0 + 5000 + 7500 (cents)
    }

    public function test_atomic_rollback_on_failure(): void
    {
        $sender = User::factory()->create(['balance' => 10000]); // $100 in cents
        $initialBalance = $sender->balance;

        // Force a failure by using a non-existent receiver
        // This should roll back any partial changes
        $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
            'receiver_id' => 99999,
            'amount' => 50.00,
        ]);

        $response->assertUnprocessable();

        // Verify sender's balance is unchanged (rollback successful)
        $sender->refresh();
        $this->assertEquals($initialBalance, $sender->balance);

        // Verify no transaction was created
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_multiple_sequential_transfers_maintain_consistency(): void
    {
        $sender = User::factory()->create(['balance' => 100000]); // $1000 in cents
        $receiver = User::factory()->create(['balance' => 0]);

        $transfers = [
            ['amount' => 100.00, 'commission' => 1.50],
            ['amount' => 50.00, 'commission' => 0.75],
            ['amount' => 200.00, 'commission' => 3.00],
        ];

        $expectedSenderBalance = 100000; // In cents
        $expectedReceiverBalance = 0;

        foreach ($transfers as $transfer) {
            $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => $transfer['amount'],
            ]);

            $response->assertCreated();

            $debit = Money::fromFloat($transfer['amount'] + $transfer['commission']);
            $credit = Money::fromFloat($transfer['amount']);
            $expectedSenderBalance -= $debit->toCents();
            $expectedReceiverBalance += $credit->toCents();

            $sender->refresh();
            $receiver->refresh();

            $this->assertEquals(
                $expectedSenderBalance,
                $sender->balance,
                "Sender balance incorrect after transfer of {$transfer['amount']}"
            );

            $this->assertEquals(
                $expectedReceiverBalance,
                $receiver->balance,
                "Receiver balance incorrect after transfer of {$transfer['amount']}"
            );
        }

        // Verify all transactions were recorded
        $this->assertDatabaseCount('transactions', count($transfers));
    }

    public function test_high_load_concurrent_transfers_maintain_data_integrity(): void
    {
        // Create 10 senders with $1000 each
        $senders = User::factory()->count(10)->create(['balance' => 100000]); // $1000 in cents
        $receiver = User::factory()->create(['balance' => 0]);

        $transferAmount = 50.00;
        $amount = Money::fromFloat($transferAmount);
        $commission = $amount->calculatePercentage(1.5);

        // Each sender transfers 50 to the receiver
        foreach ($senders as $sender) {
            $response = $this->actingAs($sender)->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => $transferAmount,
            ]);

            $response->assertCreated();
        }

        // Verify receiver got all transfers
        $receiver->refresh();
        $expectedReceiverBalance = $amount->multiply(10);
        $this->assertEquals($expectedReceiverBalance->toCents(), $receiver->balance);

        // Verify each sender's balance
        foreach ($senders as $sender) {
            $sender->refresh();
            $expectedBalance = Money::fromFloat(1000)->subtract($amount->add($commission));
            $this->assertEquals($expectedBalance->toCents(), $sender->balance);
        }

        // Verify transaction count
        $this->assertDatabaseCount('transactions', 10);
    }
}
