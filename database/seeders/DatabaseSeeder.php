<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the "Demo User"
        $user = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@groceria.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'date_of_birth' => '1995-05-15',
            'wallet_balance' => 250.00,
            'settings' => [
                'dark_mode' => false,
                'notifications_orders' => true,
                'notifications_promos' => false,
            ]
        ]);

        // 2. Add Addresses
        Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'address_line' => '1234 Park Avenue, Apt 12B, New York, NY',
            'is_default' => true
        ]);
        
        Address::create([
            'user_id' => $user->id,
            'label' => 'Office',
            'address_line' => '5678 Tech Park, Silicon Valley, CA',
            'is_default' => false
        ]);

        // 3. Create Categories (Matching Figma Screenshot)
        $categories = [
            ['name' => 'Fruits',       'bg_color' => '#FFF3E0', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/3081/3081902.png'],
            ['name' => 'Veggies',      'bg_color' => '#E8F5E9', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/2329/2329903.png'],
            ['name' => 'Meats',        'bg_color' => '#FFEBEE', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/10700/10700990.png'],
            ['name' => 'Seafood',      'bg_color' => '#E3F2FD', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/2921/2921822.png'],
            ['name' => 'Dairy & Eggs', 'bg_color' => '#FFFDE7', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/3050/3050158.png'],
            ['name' => 'Bakery',       'bg_color' => '#EFEBE9', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/992/992747.png'],
            ['name' => 'Pantry',       'bg_color' => '#F3E5F5', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/737/737967.png'],
            ['name' => 'Beverages',    'bg_color' => '#E0F7FA', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/2405/2405479.png'],
            ['name' => 'Snacks',       'bg_color' => '#FCE4EC', 'image_url' => 'https://cdn-icons-png.flaticon.com/512/2553/2553691.png'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 4. Retrieve Category IDs (To link products correctly)
        // We look them up by name so we don't get "Undefined Variable" errors
        $catVeggiesId = Category::where('name', 'Veggies')->value('id');
        $catFruitsId  = Category::where('name', 'Fruits')->value('id');
        $catMeatsId   = Category::where('name', 'Meats')->value('id');

        // 5. Create Products
        // --- Veggies ---
        $p1 = Product::create([
            'category_id' => $catVeggiesId,
            'name' => 'Broccoli Fresh Green',
            'description' => 'Fresh hydroponic broccoli rich in vitamins.',
            'price' => 3.29,
            'unit' => 'lb',
            'rating' => 4.8,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/234/234092.png',
            'is_featured' => true
        ]);

        Product::create([
            'category_id' => $catVeggiesId,
            'name' => 'Organic Tomato Roma',
            'description' => 'Juicy red tomatoes perfect for salads.',
            'price' => 2.49,
            'unit' => 'lb',
            'rating' => 4.5,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/1202/1202125.png',
            'is_featured' => false
        ]);

        // --- Fruits ---
        $p3 = Product::create([
            'category_id' => $catFruitsId,
            'name' => 'Banana Cavendish',
            'description' => 'Sweet and creamy bananas from local farms.',
            'price' => 1.29,
            'unit' => 'bunch',
            'rating' => 4.9,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2829/2829873.png',
            'is_featured' => true
        ]);

        Product::create([
            'category_id' => $catFruitsId,
            'name' => 'Red Apple',
            'description' => 'Crisp and sweet apples.',
            'price' => 4.99,
            'unit' => 'kg',
            'rating' => 4.7,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/415/415733.png',
            'is_featured' => false
        ]);

        // --- Meat ---
        Product::create([
            'category_id' => $catMeatsId,
            'name' => 'Chicken Breast',
            'description' => 'Boneless skinless chicken breast.',
            'price' => 8.99,
            'unit' => 'kg',
            'rating' => 4.6,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/10700/10700990.png',
            'is_featured' => true
        ]);

        // 6. Add Favorites
        $user->favorites()->attach([$p1->id, $p3->id]);
    }
}