<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'contact_info',
        'location',
        'images',
    ];

    protected $casts = [
        'contact_info' => 'array',
        'location' => 'array',
        'images' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loyaltyCards()
    {
        return $this->hasMany(LoyaltyCard::class);
    }

    public function shopLocations()
    {
        return $this->hasMany(ShopLocation::class);
    }
}
