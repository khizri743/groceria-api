<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'description', 'price', 'unit', 
        'image_url', 'rating', 'is_featured',
        // New Fields:
        'discount_percent', 'tags', 'gallery_images', 
        'origin_details', 'harvest_date', 'specifications'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'tags' => 'array',            // Automatically convert JSON to Array
        'gallery_images' => 'array',  // Automatically convert JSON to Array
        'specifications' => 'array',  // Automatically convert JSON to Array
        'harvest_date' => 'date',
    ];

    // 👇 This is the missing function that caused the error!
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}