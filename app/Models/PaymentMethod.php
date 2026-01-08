<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'card_holder_name', 'brand', 
        'last_four', 'expiry_date', 'is_default'
    ];
}