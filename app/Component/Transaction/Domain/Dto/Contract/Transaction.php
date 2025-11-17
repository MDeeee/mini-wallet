<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Domain\Dto\Contract;

interface Transaction
{
    public function senderId(): int;
    public function receiverId(): int;
    public function amount(): int;
    public function commissionFee(): int;
    public function status(): string;
}
