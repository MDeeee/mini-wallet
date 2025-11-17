<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

final class TransferMoneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'integer', 'exists:users,id', 'different:sender_id'],
            'amount'      => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sender_id' => auth()->user()?->id,
        ]);
    }

    public function receiverId(): int
    {
        return $this->integer('receiver_id');
    }

    public function amount(): float
    {
        return $this->float('amount');
    }

    public function senderId(): int
    {
        return $this->integer('sender_id');
    }
}
