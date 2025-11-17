<?php

namespace App\Component\Transaction\Data\EntityFactory;

use App\Component\Transaction\Data\Entity\TransactionEntity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionEntityFactory extends Factory
{
    protected $model = TransactionEntity::class;

    public function definition(): array
    {
        $sender = User::factory()->create(['balance' => 100000]); // $1000 in cents
        $receiver = User::factory()->create(['balance' => 50000]); // $500 in cents
        $amountInCents = $this->faker->numberBetween(1000, 50000); // $10 to $500 in cents
        $commissionInCents = (int) round($amountInCents * 0.015);

        return [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amountInCents,
            'commission_fee' => $commissionInCents,
            'status' => 'completed',
            'created_at' => now(),
        ];
    }
}
