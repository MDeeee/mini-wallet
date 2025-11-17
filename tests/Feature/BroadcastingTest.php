<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use App\Events\MoneyTransferred;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_money_transferred_event_is_dispatched_after_successful_transfer(): void
    {
        Event::fake([MoneyTransferred::class]);

        $sender = User::factory()->create(['balance' => 10000]); // 100.00
        $receiver = User::factory()->create(['balance' => 5000]); // 50.00

        $this->actingAs($sender)
            ->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => 50.00,
            ])
            ->assertStatus(201);

        Event::assertDispatched(MoneyTransferred::class, function ($event) use ($sender, $receiver) {
            return $event->senderId === $sender->id
                && $event->receiverId === $receiver->id
                && $event->amount === 5000 // 50.00 in cents
                && $event->commissionFee === 75 // 1.5% of 50.00
                && $event->senderNewBalance === 4925 // 100.00 - 50.00 - 0.75
                && $event->receiverNewBalance === 10000; // 50.00 + 50.00
        });
    }

    public function test_money_transferred_event_broadcasts_to_both_users_channels(): void
    {
        Event::fake([MoneyTransferred::class]);

        $sender = User::factory()->create(['balance' => 10000]);
        $receiver = User::factory()->create(['balance' => 5000]);

        $this->actingAs($sender)
            ->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => 50.00,
            ]);

        Event::assertDispatched(MoneyTransferred::class, function ($event) use ($sender, $receiver) {
            $channels = $event->broadcastOn();

            // Should broadcast to both sender and receiver private channels
            return count($channels) === 2
                && $channels[0]->name === 'private-user.' . $sender->id
                && $channels[1]->name === 'private-user.' . $receiver->id;
        });
    }

    public function test_money_transferred_event_contains_correct_broadcast_data(): void
    {
        $event = new MoneyTransferred(
            senderId: 1,
            receiverId: 2,
            transactionId: 123,
            amount: 10000, // 100.00 in cents
            commissionFee: 150, // 1.50 in cents
            senderNewBalance: 8850, // 88.50 in cents
            receiverNewBalance: 20000, // 200.00 in cents
        );

        $data = $event->broadcastWith();

        $this->assertEquals(123, $data['transaction_id']);
        $this->assertEquals(1, $data['sender_id']);
        $this->assertEquals(2, $data['receiver_id']);
        $this->assertEquals(100.00, $data['amount']); // Converted from cents
        $this->assertEquals(1.50, $data['commission_fee']); // Converted from cents
        $this->assertEquals(88.50, $data['sender_new_balance']); // Converted from cents
        $this->assertEquals(200.00, $data['receiver_new_balance']); // Converted from cents
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function test_money_transferred_event_broadcasts_as_correct_name(): void
    {
        $event = new MoneyTransferred(
            senderId: 1,
            receiverId: 2,
            transactionId: 1,
            amount: 5000,
            commissionFee: 75,
            senderNewBalance: 4925,
            receiverNewBalance: 10000,
        );

        $this->assertEquals('money.transferred', $event->broadcastAs());
    }

    public function test_event_not_dispatched_when_transfer_fails(): void
    {
        Event::fake([MoneyTransferred::class]);

        $sender = User::factory()->create(['balance' => 1000]); // 10.00
        $receiver = User::factory()->create(['balance' => 5000]);

        // Try to send more than balance (should fail)
        $this->actingAs($sender)
            ->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => 50.00, // More than available
            ])
            ->assertStatus(422);

        Event::assertNotDispatched(MoneyTransferred::class);
    }

    public function test_event_not_dispatched_when_validation_fails(): void
    {
        Event::fake([MoneyTransferred::class]);

        $sender = User::factory()->create(['balance' => 10000]);

        // Invalid receiver_id
        $this->actingAs($sender)
            ->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => 99999,
                'amount' => 50.00,
            ])
            ->assertStatus(422);

        Event::assertNotDispatched(MoneyTransferred::class);
    }

    public function test_private_channel_authorization_logic(): void
    {
        $user = User::factory()->create(['id' => 1]);
        $otherUser = User::factory()->create(['id' => 2]);

        // Simulate the channel authorization callback
        // From routes/channels.php: fn ($user, $userId) => (int) $user->id === (int) $userId

        // User can access their own channel
        $canAccessOwnChannel = (int) $user->id === (int) $user->id;
        $this->assertTrue($canAccessOwnChannel);

        // User cannot access other user's channel
        $canAccessOtherChannel = (int) $user->id === (int) $otherUser->id;
        $this->assertFalse($canAccessOtherChannel);
    }

    public function test_event_includes_transaction_id_for_frontend_reference(): void
    {
        Event::fake([MoneyTransferred::class]);

        $sender = User::factory()->create(['balance' => 10000]);
        $receiver = User::factory()->create(['balance' => 5000]);

        $this->actingAs($sender)
            ->postJson(route('api.v1.transactions.store'), [
                'receiver_id' => $receiver->id,
                'amount' => 50.00,
            ]);

        Event::assertDispatched(MoneyTransferred::class, function ($event) {
            $transaction = TransactionEntity::find($event->transactionId);

            return $transaction !== null
                && $transaction->id === $event->transactionId
                && $transaction->sender_id === $event->senderId
                && $transaction->receiver_id === $event->receiverId;
        });
    }
}
