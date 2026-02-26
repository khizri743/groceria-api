<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'image_url', 
        'author_name', 'read_time', 'published_date', 'tags'
    ];

    protected $casts = [
        'tags' => 'array', // Automatically handle JSON tags
        'published_date' => 'date'
    ];
}