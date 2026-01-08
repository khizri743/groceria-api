<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_profile_fields_to_users_table.php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // We might have added phone earlier, checking to be safe
        if (!Schema::hasColumn('users', 'phone')) {
            $table->string('phone')->nullable();
        }
        $table->date('date_of_birth')->nullable();
        $table->string('avatar_url')->nullable(); // Stores path to image
        $table->decimal('wallet_balance', 10, 2)->default(0.00); // Screen 1
        
        // Settings (storing as JSON is efficient for toggles)
        $table->json('settings')->nullable(); 
        // Example JSON: { "dark_mode": true, "notify_orders": true, "notify_promos": false }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
