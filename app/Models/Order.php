<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // ─── Status Constants ──────────────────────────────

    public const STATUS_PENDING = 'pending';

    public const STATUS_FOR_DELIVERY = 'for_delivery';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELED = 'canceled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_FOR_DELIVERY,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELED,
    ];

    protected $fillable = [
        'user_id',
        'total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    // ─── Relationships ─────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
