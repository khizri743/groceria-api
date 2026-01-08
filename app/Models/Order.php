<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'guest_info', 'order_number', 'status', 
        'total_amount', 'delivery_fee', 'discount_amount', 
        'payment_method', 'payment_status', 'delivery_address', 
        'delivery_date', 'points_earned'
    ];

    protected $casts = [
        'guest_info' => 'array', // Automatically handle JSON
        'delivery_date' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}