<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

/**
 * Private channel for user-specific events (money transfers, balance updates)
 * Only authenticated user can listen to their own channel
 */
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
