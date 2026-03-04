<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'driver_id', // <--- Added (Crucial for Admin Panel assignment)
        'guest_info', 
        'order_number', 
        'status', 
        'total_amount', 
        'delivery_fee', 
        'discount_amount',
        'tip_amount', // <--- Added (For Tipping feature)
        'payment_method', 
        'payment_status', 
        'delivery_address', 
        'delivery_date', 
        'estimated_arrival_time', // <--- Added (For Tracking screen)
        'points_earned'
    ];

    protected $casts = [
        'guest_info' => 'array', 
        'delivery_date' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Virtual Attribute to get Customer Name (User OR Guest)
     * Usage: $order->customer_name
     */
    public function getCustomerNameAttribute()
    {
        // 1. If it's a registered user
        if ($this->user) {
            return $this->user->name;
        }

        // 2. If it's a guest (check the JSON column)
        if ($this->guest_info) {
            // Check if it's an array or object
            $info = is_string($this->guest_info) ? json_decode($this->guest_info, true) : $this->guest_info;
            return $info['name'] ?? 'Guest';
        }

        return 'Unknown';
    }
}