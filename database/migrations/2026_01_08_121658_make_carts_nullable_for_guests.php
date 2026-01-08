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
    Schema::table('carts', function (Blueprint $table) {
        // Allow user_id to be NULL (for guests)
        $table->foreignId('user_id')->nullable()->change();
        
        // Add guest_id (e.g., a unique device ID from the phone)
        $table->string('guest_id')->nullable()->after('user_id')->index();
    });
}

public function down()
{
    Schema::table('carts', function (Blueprint $table) {
        $table->foreignId('user_id')->nullable(false)->change();
        $table->dropColumn('guest_id');
    });
}
};
