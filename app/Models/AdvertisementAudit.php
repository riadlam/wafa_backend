<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertisement_id',
        'action',
        'performed_by',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}


