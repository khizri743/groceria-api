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
    Schema::table('orders', function (Blueprint $table) {
        // 1. Drop the old hardcoded columns
        // $table->dropColumn(['driver_name', 'driver_phone', 'driver_avatar', 'driver_vehicle']);
        
        // 2. Add the relationship link
        $table->foreignId('driver_id')->nullable()->after('user_id')->constrained('drivers')->onDelete('set null');
    });
}

public function down()
{
    // If we roll back, we lose the ID and just add text columns back
    Schema::table('orders', function (Blueprint $table) {
        $table->dropForeign(['driver_id']);
        $table->dropColumn('driver_id');
        $table->string('driver_name')->nullable();
        $table->string('driver_phone')->nullable();
        $table->string('driver_avatar')->nullable();
        $table->string('driver_vehicle')->nullable();
    });
}
};
