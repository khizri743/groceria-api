<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Address;
use App\Models\Voucher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. USERS
        // ==========================================
        
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@groceria.com',
            'password' => Hash::make('password'),
            'phone' => '+0000000000',
            'role' => 'admin',
            'wallet_balance' => 0,
        ]);

        // Demo Customer (Sarah)
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

        // Addresses
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

        // ==========================================
        // 2. CATEGORIES
        // ==========================================
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

        // Helper to get IDs
        $cats = Category::pluck('id', 'name'); 

        // ==========================================
        // 3. PRODUCTS
        // ==========================================

        // --- VEGGIES ---
        $corn = Product::create([
            'category_id' => $cats['Veggies'],
            'name' => 'Fresh sweet corn',
            'description' => 'Crisp, juicy, and naturally sweet great for grilling.',
            'price' => 5.49,
            'discount_percent' => 0,
            'unit' => 'each',
            'rating' => 4.8,
            'is_featured' => true,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/1147/1147593.png',
            'gallery_images' => ['https://cdn-icons-png.flaticon.com/512/1147/1147593.png', 'https://cdn-icons-png.flaticon.com/512/765/765544.png'],
            'tags' => ['Organic', 'Non-GMO', 'Gluten-free', 'Vegan friendly'],
            'origin_details' => 'Grown in Illinois. Harvested at peak ripeness.',
            'harvest_date' => now()->subDay(),
            'specifications' => ['weight_desc' => '250-300 g', 'shelf_life' => '3-4 days', 'type' => 'Whole ear']
        ]);

        $frozenCorn = Product::create([
            'category_id' => $cats['Veggies'],
            'name' => 'Frozen corn kernels',
            'description' => 'Quick frozen sweet corn.',
            'price' => 3.49,
            'discount_percent' => 30,
            'unit' => 'pack',
            'rating' => 4.5,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2329/2329903.png',
            'tags' => ['Frozen', 'Non-GMO'],
            'specifications' => ['weight_desc' => '454 g', 'shelf_life' => '6 months', 'type' => 'Frozen']
        ]);

        Product::create([
            'category_id' => $cats['Veggies'],
            'name' => 'Baby Spinach',
            'description' => 'Organic local farm spinach.',
            'price' => 1.99,
            'discount_percent' => 30,
            'unit' => 'pack',
            'rating' => 4.8,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/234/234092.png',
            'tags' => ['vegan', 'organic', 'low-carb']
        ]);

        Product::create([
            'category_id' => $cats['Veggies'],
            'name' => 'Red Bell Pepper',
            'description' => 'Sweet and crunchy red peppers.',
            'price' => 0.99,
            'unit' => 'each',
            'rating' => 4.7,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2909/2909787.png',
            'tags' => ['vegan', 'fresh']
        ]);

        // --- FRUITS ---
        $banana = Product::create([
            'category_id' => $cats['Fruits'],
            'name' => 'Banana Cavendish',
            'description' => 'Sweet and creamy bananas.',
            'price' => 1.29,
            'unit' => 'bunch',
            'rating' => 4.9,
            'is_featured' => true,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2829/2829873.png',
            'tags' => ['organic', 'vegan']
        ]);

        Product::create([
            'category_id' => $cats['Fruits'],
            'name' => 'Green Apple (Granny Smith)',
            'description' => 'Tart and crisp green apples.',
            'price' => 3.99,
            'unit' => 'kg',
            'rating' => 4.6,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/415/415733.png',
            'tags' => ['fresh', 'vegan']
        ]);

        Product::create([
            'category_id' => $cats['Fruits'],
            'name' => 'Strawberries',
            'description' => 'Sweet red strawberries.',
            'price' => 4.99,
            'discount_percent' => 10,
            'unit' => 'pack',
            'rating' => 4.8,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/590/590772.png',
            'harvest_date' => now()->subDays(2),
            'tags' => ['fresh', 'organic']
        ]);

        // --- MEATS ---
        Product::create([
            'category_id' => $cats['Meats'],
            'name' => 'Premium Ribeye Steak',
            'description' => 'Grass-fed beef ribeye steak.',
            'price' => 24.99,
            'unit' => 'kg',
            'rating' => 5.0,
            'is_featured' => true,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/3143/3143643.png',
            'tags' => ['grass-fed', 'keto'],
            'specifications' => ['weight_desc' => '1kg', 'type' => 'Beef', 'storage' => 'Refrigerated']
        ]);

        Product::create([
            'category_id' => $cats['Meats'],
            'name' => 'Chicken Breast Fillet',
            'description' => 'Lean boneless chicken breast.',
            'price' => 9.50,
            'unit' => 'kg',
            'rating' => 4.7,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/10700/10700990.png',
            'tags' => ['lean', 'poultry']
        ]);

        // --- SEAFOOD ---
        Product::create([
            'category_id' => $cats['Seafood'],
            'name' => 'Atlantic Salmon Fillet',
            'description' => 'Fresh Atlantic salmon, rich in Omega-3.',
            'price' => 15.99,
            'unit' => 'lb',
            'rating' => 4.9,
            'is_featured' => true,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2921/2921822.png',
            'harvest_date' => now()->subDays(1),
            'tags' => ['fresh', 'omega-3']
        ]);

        Product::create([
            'category_id' => $cats['Seafood'],
            'name' => 'Tiger Prawns (Large)',
            'description' => 'Juicy large prawns, perfect for grilling.',
            'price' => 18.99,
            'unit' => 'kg',
            'rating' => 4.8,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/1998/1998064.png',
            'tags' => ['frozen']
        ]);

        // --- DAIRY ---
        Product::create([
            'category_id' => $cats['Dairy & Eggs'],
            'name' => 'Whole Milk',
            'description' => 'Farm fresh whole milk.',
            'price' => 1.99,
            'unit' => 'bottle',
            'rating' => 4.5,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2405/2405452.png',
            'tags' => ['fresh', 'calcium']
        ]);

        Product::create([
            'category_id' => $cats['Dairy & Eggs'],
            'name' => 'Cheddar Cheese Block',
            'description' => 'Aged cheddar cheese.',
            'price' => 4.49,
            'unit' => 'block',
            'rating' => 4.6,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/8202/8202495.png',
            'tags' => ['aged']
        ]);

        Product::create([
            'category_id' => $cats['Dairy & Eggs'],
            'name' => 'Free Range Eggs',
            'description' => 'Large brown eggs, dozen.',
            'price' => 3.29,
            'unit' => 'dozen',
            'rating' => 4.8,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/837/837560.png',
            'tags' => ['organic', 'free-range']
        ]);

        // --- BAKERY ---
        Product::create([
            'category_id' => $cats['Bakery'],
            'name' => 'Sourdough Bread',
            'description' => 'Artisan sourdough loaf.',
            'price' => 3.99,
            'unit' => 'loaf',
            'rating' => 4.7,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/992/992747.png',
            'tags' => ['freshly-baked']
        ]);

        Product::create([
            'category_id' => $cats['Bakery'],
            'name' => 'Chocolate Croissant',
            'description' => 'Buttery croissant with rich chocolate.',
            'price' => 2.50,
            'unit' => 'each',
            'rating' => 4.9,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/3014/3014522.png',
            'tags' => ['sweet', 'pastry']
        ]);

        // --- PANTRY ---
        Product::create([
            'category_id' => $cats['Pantry'],
            'name' => 'Olive Oil (Extra Virgin)',
            'description' => 'Cold pressed olive oil.',
            'price' => 12.99,
            'unit' => 'bottle',
            'rating' => 4.8,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/737/737967.png',
            'tags' => ['imported', 'organic']
        ]);

        Product::create([
            'category_id' => $cats['Pantry'],
            'name' => 'Basmati Rice',
            'description' => 'Long grain aromatic rice.',
            'price' => 8.99,
            'unit' => 'bag',
            'rating' => 4.6,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2829/2829853.png',
            'tags' => ['gluten-free']
        ]);

        // --- BEVERAGES ---
        Product::create([
            'category_id' => $cats['Beverages'],
            'name' => 'Orange Juice',
            'description' => '100% freshly squeezed orange juice.',
            'price' => 3.99,
            'unit' => 'bottle',
            'rating' => 4.7,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2442/2442019.png',
            'tags' => ['no-sugar', 'vitamin-c']
        ]);

        Product::create([
            'category_id' => $cats['Beverages'],
            'name' => 'Cola Soda',
            'description' => 'Refreshing carbonated soft drink.',
            'price' => 1.50,
            'discount_percent' => 10,
            'unit' => 'can',
            'rating' => 4.2,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2405/2405479.png',
            'tags' => ['chilled']
        ]);

        // --- SNACKS ---
        Product::create([
            'category_id' => $cats['Snacks'],
            'name' => 'Salted Potato Chips',
            'description' => 'Classic salted potato chips.',
            'price' => 1.99,
            'unit' => 'bag',
            'rating' => 4.4,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/2553/2553691.png',
            'tags' => ['crispy']
        ]);

        Product::create([
            'category_id' => $cats['Snacks'],
            'name' => 'Roasted Almonds',
            'description' => 'Healthy salted roasted almonds.',
            'price' => 5.99,
            'unit' => 'pack',
            'rating' => 4.8,
            'image_url' => 'https://cdn-icons-png.flaticon.com/512/1269/1269032.png',
            'tags' => ['healthy', 'protein']
        ]);

        // ==========================================
        // 4. FAVORITES
        // ==========================================
        $user->favorites()->attach([$corn->id, $banana->id]);

        // ==========================================
        // 5. VOUCHERS
        // ==========================================
        Voucher::create([
            'code' => 'WELCOME20',
            'description' => '20% OFF First Order',
            'type' => 'percent',
            'value' => 20.00, 
            'expires_at' => now()->addMonth()
        ]);

        Voucher::create([
            'code' => 'SAVE5',
            'description' => '$5 OFF Shipping',
            'type' => 'fixed',
            'value' => 5.00,
            'expires_at' => now()->addMonth()
        ]);

        // ==========================================
        // 6. DRIVERS
        // ==========================================
        $driverJohn = \App\Models\Driver::create([
            'name' => 'John M.',
            'phone' => '+1234567890',
            'vehicle_type' => 'Groceria Express Van',
            'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/1077/1077114.png',
            'status' => 'busy',
            // REMOVED GPS coordinates to match your database
        ]);

        // ==========================================
        // 7. ORDERS
        // ==========================================

        // Order 1: ACTIVE (Assigned to John)
        $activeOrder = \App\Models\Order::create([
            'user_id' => $user->id,
            'driver_id' => $driverJohn->id, 
            'order_number' => '#4526',
            'status' => 'shipped',
            'total_amount' => 16.22,
            'delivery_fee' => 5.00,
            'discount_amount' => 1.25,
            'payment_method' => 'groceria_pay',
            'payment_status' => 'paid',
            'delivery_address' => '1234 Park Avenue, Apt 12B, New York, NY',
            'delivery_date' => now(),
            'points_earned' => 112,
            'estimated_arrival_time' => '5:30 - 6:00 PM'
        ]);

        \App\Models\OrderItem::create(['order_id' => $activeOrder->id, 'product_id' => $corn->id, 'quantity' => 1, 'price' => 5.49]);
        \App\Models\OrderItem::create(['order_id' => $activeOrder->id, 'product_id' => $frozenCorn->id, 'quantity' => 2, 'price' => 3.49]);

        // Order 2: PAST (Delivered)
        $pastOrder = \App\Models\Order::create([
            'user_id' => $user->id,
            'driver_id' => $driverJohn->id,
            'order_number' => '#4521',
            'status' => 'delivered',
            'total_amount' => 18.99,
            'delivery_fee' => 5.00,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'delivery_address' => '1234 Park Avenue, Apt 12B, New York, NY',
            'delivery_date' => now()->subDays(5),
            'points_earned' => 180
        ]);
        
        \App\Models\OrderItem::create(['order_id' => $pastOrder->id, 'product_id' => $banana->id, 'quantity' => 1, 'price' => 1.99]);

        // ==========================================
        // 8. CHAT HISTORY
        // ==========================================
        DB::table('messages')->insert([
            'order_id' => $activeOrder->id,
            'user_id' => null,
            'driver_id' => $driverJohn->id,
            'message' => 'Hello! I have your order and I am on my way 🚗',
            'sender_type' => 'driver',
            'created_at' => now()->subMinutes(15),
        ]);

        DB::table('messages')->insert([
            'order_id' => $activeOrder->id,
            'user_id' => null,
            'driver_id' => $driverJohn->id,
            'message' => 'I will be there in about 12 minutes',
            'sender_type' => 'driver',
            'created_at' => now()->subMinutes(14),
        ]);

        DB::table('messages')->insert([
            'order_id' => $activeOrder->id,
            'user_id' => $user->id,
            'driver_id' => $driverJohn->id,
            'message' => 'I am waiting outside',
            'sender_type' => 'user',
            'created_at' => now()->subMinutes(5),
        ]);


        // ... previous chat/order code ...

        // ==========================================
        // 11. ARTICLES
        // ==========================================
        
        // Article 1: The one in the Screenshot
        \App\Models\Article::create([
            'title' => '5 Veggies to Boost Your Immune System',
            'image_url' => 'https://cdn.pixabay.com/photo/2016/09/15/19/24/salad-1672505_1280.jpg',
            'author_name' => 'Dr. Sarah Chen',
            'read_time' => '4 min read',
            'published_date' => '2024-10-15',
            'tags' => ['Nutrition', 'Health'],
            'content' => '
                <p>Maintaining a strong immune system is essential for overall health...</p>
                <h3>1. Spinach</h3>
                <p>Rich in vitamin C, beta-carotene, and antioxidants, spinach is a powerhouse...</p>
                <h3>2. Broccoli</h3>
                <p>Packed with vitamins A, C, and E, as well as fiber and antioxidants...</p>
                <h3>3. Bell Peppers</h3>
                <p>Red bell peppers contain twice as much vitamin C as citrus fruits...</p>
            '
        ]);

        // Article 2: Storage Tips
        \App\Models\Article::create([
            'title' => 'How to Keep Fruits Fresh Longer',
            'image_url' => 'https://cdn.pixabay.com/photo/2017/05/07/08/56/pancakes-2291908_1280.jpg',
            'author_name' => 'Mike Ross',
            'read_time' => '3 min read',
            'published_date' => '2024-10-10',
            'tags' => ['Storage Tips', 'Hacks'],
            'content' => '<p>Stop throwing away spoiled fruit with these simple storage hacks...</p>'
        ]);

        // Article 3: Recipes
        \App\Models\Article::create([
            'title' => 'Quick & Healthy Breakfast Ideas',
            'image_url' => 'https://cdn.pixabay.com/photo/2016/11/06/23/31/breakfast-1804457_1280.jpg',
            'author_name' => 'Chef Ramsay',
            'read_time' => '6 min read',
            'published_date' => '2024-10-05',
            'tags' => ['Recipes', 'Breakfast'],
            'content' => '<p>Start your day right with these protein-packed breakfast bowls...</p>'
        ]);
    }
}