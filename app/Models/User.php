<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
         'name',
        'email',
        'password',
        'phone',          // <--- Add this
        'date_of_birth',  // <--- Add this
        'avatar_url',     // <--- Add this
        'settings',       // <--- Add this
        'wallet_balance', // <--- Add this
        'google_id',      // (If you added social login fields)
        'apple_id',
        'pin_code',
        'is_biometric_enabled'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses() {
    return $this->hasMany(Address::class);
}

public function favorites() {
    return $this->belongsToMany(Product::class, 'favorites', 'user_id', 'product_id')->withTimestamps();
}

 protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? url('storage/' . $value) : null,
        );
    }

public function paymentMethods() {
    return $this->hasMany(PaymentMethod::class);
}

// Helper to cast settings automatically
protected $casts = [
    'settings' => 'array',
    'wallet_balance' => 'decimal:2',
];
}
