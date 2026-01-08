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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->string('name'); // e.g., "Banana Cavendish"
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2); // e.g., 1.29
        $table->string('unit')->default('pcs'); // e.g., "lb", "kg", "pcs"
        $table->string('image_url')->nullable();
        $table->decimal('rating', 2, 1)->default(5.0); // e.g., 4.8
        $table->boolean('is_featured')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
