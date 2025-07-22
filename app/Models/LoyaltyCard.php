<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'logo_url',
        'color',
        'total_stamps',
        'description',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function userCards()
    {
        return $this->hasMany(UserLoyaltyCard::class);
    }
}
