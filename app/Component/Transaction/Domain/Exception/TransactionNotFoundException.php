<?php

declare(strict_types=1);

namespace App\Component\Transaction\Domain\Exception;

use Exception;

final class TransactionNotFoundException extends Exception
{
    public static function withId(int $id): self
    {
        return new self("Transaction not found with ID: {$id}");
    }
}
