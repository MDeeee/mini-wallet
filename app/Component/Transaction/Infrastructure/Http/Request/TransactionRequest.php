<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Infrastructure\Http\Request;

use App\Component\Transaction\Domain\Dto\Contract\Transaction;
use App\Component\Transaction\Domain\Enum\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest implements Transaction
{
    public function rules(): array
    {
        return [
            'sender_id'      => ['required', 'integer', 'exists:users,id'],
            'receiver_id'    => ['required', 'integer', 'exists:users,id', 'different:sender_id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'commission_fee' => ['required', 'numeric', 'min:0'],
            'status'         => ['required', 'string', Rule::in(TransactionStatus::values())],
        ];
    }

    public function senderId(): int
    {
        return $this->integer('sender_id');
    }

    public function receiverId(): int
    {
        return $this->integer('receiver_id');
    }

    public function amount(): int
    {
        return (int) round($this->float('amount') * 100); // Convert dollars to cents
    }

    public function commissionFee(): int
    {
        return (int) round($this->float('commission_fee') * 100); // Convert dollars to cents
    }

    public function status(): string
    {
        return $this->string('status')->value();
    }
}
