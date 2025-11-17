<?php

declare(strict_types = 1);

namespace App\Component\Transaction\Data\Entity;

use App\Component\Transaction\Data\EntityFactory\TransactionEntityFactory;
use App\Component\Transaction\Domain\Enum\TransactionStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionEntity extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    public $timestamps = false;

    /** @var array<int, string> */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
        'status',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'amount' => 'integer',
        'commission_fee' => 'integer',
        'status' => TransactionStatus::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    protected static function newFactory(): TransactionEntityFactory
    {
        return TransactionEntityFactory::new();
    }
}
