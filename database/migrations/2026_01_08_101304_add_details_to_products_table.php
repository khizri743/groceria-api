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
    Schema::table('products', function (Blueprint $table) {
        // 1. Discount Badge (-30%)
        if (!Schema::hasColumn('products', 'discount_percent')) {
            $table->integer('discount_percent')->default(0)->after('price');
        }

        // 2. Attributes/Tags (Organic, Non-GMO, Vegan) - Saved as ["Organic", "Non-GMO"]
        if (!Schema::hasColumn('products', 'tags')) {
            $table->json('tags')->nullable()->after('description');
        }

        // 3. Image Gallery (For the slider) - Saved as ["url1.jpg", "url2.jpg"]
        $table->json('gallery_images')->nullable()->after('image_url');

        // 4. Product Origin Text
        $table->text('origin_details')->nullable()->after('tags');

        // 5. Freshness Logic (Picked Yesterday)
        $table->date('harvest_date')->nullable()->after('origin_details');

        // 6. Tech Specs for Comparison (Weight, Shelf Life, Type)
        // Saved as {"shelf_life": "3-4 days", "type": "Whole ear", "weight_desc": "250g"}
        $table->json('specifications')->nullable()->after('unit');
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['discount_percent', 'tags', 'gallery_images', 'origin_details', 'harvest_date', 'specifications']);
    });
}
};
