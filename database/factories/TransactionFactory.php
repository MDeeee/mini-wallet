<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amountInCents = fake()->numberBetween(100, 100000); // $1 to $1000 in cents
        $commissionInCents = (int) round($amountInCents * 0.015);

        return [
            'sender_id' => \App\Models\User::factory(),
            'receiver_id' => \App\Models\User::factory(),
            'amount' => $amountInCents,
            'commission_fee' => $commissionInCents,
            'status' => 'completed',
            'created_at' => now(),
        ];
    }
}
