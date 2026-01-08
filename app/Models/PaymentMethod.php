<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['user_id', 'brand', 'last_four', 'expiry_date', 'is_default'];
}
