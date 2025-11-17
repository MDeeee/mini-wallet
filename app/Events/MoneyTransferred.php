<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MoneyTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly int $senderId,
        public readonly int $receiverId,
        public readonly int $transactionId,
        public readonly int $amount,
        public readonly int $commissionFee,
        public readonly int $senderNewBalance,
        public readonly int $receiverNewBalance,
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->senderId),
            new PrivateChannel('user.' . $this->receiverId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'money.transferred';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
            'amount' => $this->amount / 100, // Convert cents to dollars for frontend
            'commission_fee' => $this->commissionFee / 100,
            'sender_new_balance' => $this->senderNewBalance / 100,
            'receiver_new_balance' => $this->receiverNewBalance / 100,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
