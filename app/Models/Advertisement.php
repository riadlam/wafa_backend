<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'owner_user_id',
        'title',
        'description',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'scheduled_at',
        'sent_at',
        'target_count',
        'delivered_count',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function audits()
    {
        return $this->hasMany(AdvertisementAudit::class);
    }
}


