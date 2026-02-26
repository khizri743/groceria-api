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
    // 1. Add Tip Amount to Orders table
    Schema::table('orders', function (Blueprint $table) {
        $table->decimal('tip_amount', 8, 2)->default(0.00)->after('total_amount');
    });

    // 2. Create Reviews Table
    Schema::create('reviews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained(); // The customer
        $table->foreignId('driver_id')->nullable()->constrained(); // The driver being rated
        
        $table->integer('driver_rating')->default(5); // 1-5 Stars
        $table->integer('experience_rating')->default(5); // 1-5 Stars
        $table->text('comment')->nullable(); // "Great service!"
        
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('reviews');
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn('tip_amount');
    });
}
};
