<?php

declare(strict_types=1);

namespace App\Component\Transaction\Domain\Exception;

use Exception;

final class InsufficientBalanceException extends Exception
{
    public static function forUser(int $userId, float $required, float $available): self
    {
        return new self(
            "Insufficient balance for user {$userId}. Required: {$required}, Available: {$available}"
        );
    }
}
