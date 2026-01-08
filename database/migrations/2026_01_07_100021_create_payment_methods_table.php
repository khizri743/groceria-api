<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // create_payment_methods_table.php
public function up()
{
    Schema::create('payment_methods', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        // Added this line to match the Screenshot input
        $table->string('card_holder_name'); 
        
        $table->string('brand'); // Visa, Mastercard
        $table->string('last_four'); // 4242
        $table->string('expiry_date'); // 06/27
        $table->boolean('is_default')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
