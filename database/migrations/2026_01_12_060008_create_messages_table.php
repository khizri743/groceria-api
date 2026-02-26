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
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->nullable()->constrained(); // Null if sent by driver
        $table->foreignId('driver_id')->nullable()->constrained(); // Null if sent by user
        
        $table->text('message');
        $table->string('sender_type'); // 'user' or 'driver'
        
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('messages');
}
};
