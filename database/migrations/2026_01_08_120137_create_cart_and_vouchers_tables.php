<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_cart_and_vouchers_tables.php
public function up()
{
    // 1. Vouchers Table (Coupons)
    Schema::create('vouchers', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // e.g., "WELCOME20"
        $table->string('description')->nullable(); // "20% OFF First Order"
        $table->enum('type', ['percent', 'fixed']); // % or $ off
        $table->decimal('value', 10, 2); // e.g., 20.00
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });

    // 2. Carts Table (One cart per user)
    Schema::create('carts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
    });

    // 3. Cart Items Table (The products inside the cart)
    Schema::create('cart_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->integer('quantity')->default(1);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_and_vouchers_tables');
    }
};
