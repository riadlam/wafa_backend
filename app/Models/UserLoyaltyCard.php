<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoyaltyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loyalty_card_id',
        'active_stamps',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }

    public function stamps()
    {
        return $this->hasMany(Stamp::class);
    }
}
