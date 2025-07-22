<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedemptionStatistic extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'loyalty_card_id',
    ];

    /**
     * Get the user that owns the redemption statistic.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the loyalty card that owns the redemption statistic.
     */
    public function loyaltyCard(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCard::class);
    }
}
