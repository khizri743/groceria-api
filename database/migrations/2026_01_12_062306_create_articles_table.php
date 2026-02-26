<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // "5 Veggies to Boost..."
        $table->text('content'); // HTML content (Headings, paragraphs)
        $table->string('image_url'); // Cover image
        $table->string('author_name'); // "Dr. Sarah Chen"
        $table->string('read_time'); // "4 min read"
        $table->date('published_date'); // "Oct 15, 2024"
        $table->json('tags')->nullable(); // ["Nutrition", "Health"]
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('articles');
}
};
