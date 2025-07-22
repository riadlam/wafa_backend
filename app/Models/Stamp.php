<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_loyalty_card_id',
        'added_by',
    ];

    public function userLoyaltyCard()
    {
        return $this->belongsTo(UserLoyaltyCard::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
