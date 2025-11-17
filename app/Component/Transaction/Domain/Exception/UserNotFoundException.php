<?php

declare(strict_types=1);

namespace App\Component\Transaction\Domain\Exception;

use Exception;

final class UserNotFoundException extends Exception
{
    public static function withId(int $userId): self
    {
        return new self("User with ID {$userId} not found");
    }
}
