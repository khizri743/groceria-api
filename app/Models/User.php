<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements FilamentUser // <--- Don't forget 'implements'
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'avatar_url',
        'settings',
        'wallet_balance',
        'google_id',
        'apple_id',
        'pin_code',
        'is_biometric_enabled',
        'role',        // <--- Added for RBAC
        'permissions', // <--- Added for RBAC
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',           // Merged here
            'wallet_balance' => 'decimal:2', // Merged here
            'permissions' => 'array',        // <--- Added
        ];
    }

    // --- Relationships ---

    public function addresses() {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    public function favorites() {
        return $this->belongsToMany(Product::class, 'favorites', 'user_id', 'product_id')->withTimestamps();
    }

    public function paymentMethods() {
        return $this->hasMany(PaymentMethod::class);
    }

    // --- Accessors ---

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? url('storage/' . $value) : null,
            set: fn ($value) => $value, 
        );
    }

    // --- Filament & Permissions Logic ---

    public function canAccessPanel(Panel $panel): bool
    {
        // Allow Super Admins AND Staff to login
        return in_array($this->role, ['admin', 'staff']);
    }

    // Helper to check specific permissions (Used in Policies)
    public function hasPermission($module, $accessLevel)
    {
        // Admin has access to everything
        if ($this->role === 'admin') return true;

        // Get permission for this module (default to 'none')
        // Example: $this->permissions['products'] could be 'read', 'write', or null
        $permission = $this->permissions[$module] ?? 'none';

        if ($accessLevel === 'read') {
            return in_array($permission, ['read', 'write']);
        }
        
        if ($accessLevel === 'write') {
            return $permission === 'write';
        }

        return false;
    }
}