<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    // This property tells Laravel which fields are safe to save
    protected $fillable = [
        'identifier',
        'code',
        'purpose',
        'expires_at'
    ];

    // Optional: Casts help format the data automatically
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}