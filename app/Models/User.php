<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'role',
        'plan',
        'trial_ends_at',
        'pro_ends_at',
        'fcm_tokens',
    ];
    
    protected $dates = [
        'trial_ends_at',
        'pro_ends_at',
    ];

    protected $casts = [
        'fcm_tokens' => 'array',
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function loyaltyCards()
    {
        return $this->hasMany(UserLoyaltyCard::class);
    }

    public function addedStamps()
    {
        return $this->hasMany(Stamp::class, 'added_by');
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @return \Laravel\Sanctum\NewAccessToken
     */
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
