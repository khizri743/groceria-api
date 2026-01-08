<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Create ORDERS Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // User ID (Nullable = Guest Support)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Guest Details (Name, Phone, Email - stored as JSON)
            $table->json('guest_info')->nullable();
            
            // Order Details
            $table->string('order_number')->unique(); // e.g. #93905250
            $table->string('status')->default('processing'); // pending, processing, delivered
            
            // Money Fields
            $table->decimal('total_amount', 10, 2);
            $table->decimal('delivery_fee', 8, 2)->default(5.00);
            $table->decimal('discount_amount', 8, 2)->default(0.00);
            
            // Payment Info
            $table->string('payment_method')->default('cod'); // 'groceria_pay', 'card', 'cod'
            $table->string('payment_status')->default('pending'); // 'paid', 'failed'
            
            // Delivery Info
            $table->text('delivery_address'); // Full address string
            $table->dateTime('delivery_date')->nullable();
            
            // Rewards
            $table->integer('points_earned')->default(0);

            $table->timestamps();
        });

        // 2. Create ORDER ITEMS Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Price at the moment of purchase
            $table->timestamps();
        });
        
        // 3. Add Points to Users Table (If not already there)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('points')->default(0)->after('wallet_balance');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'points')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('points');
            });
        }
    }
};