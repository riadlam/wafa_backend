<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopLocation extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'lat',
        'lng',
        'name'
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * Get the user that owns the shop location.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop that owns the location.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
