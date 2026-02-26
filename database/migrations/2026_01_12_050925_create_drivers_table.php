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
    Schema::create('drivers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // "John M."
        $table->string('phone'); // "+1234..."
        $table->string('vehicle_type'); // "Groceria Express Van"
        $table->string('avatar_url')->nullable(); 
        $table->enum('status', ['available', 'busy', 'offline'])->default('available');
        $table->timestamps();
    });
}
};
