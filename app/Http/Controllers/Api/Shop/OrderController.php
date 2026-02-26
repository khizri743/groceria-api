<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Voucher;

class OrderController extends Controller
{
    // Helper: Find the Cart
    private function getCart(Request $request)
    {
        $user = $request->user('sanctum');
        if ($user) {
            return Cart::where('user_id', $user->id)->first();
        }

        $guestId = $request->header('X-Guest-ID');
        if ($guestId) {
            return Cart::where('guest_id', $guestId)->first();
        }

        return null;
    }

    /**
     * GET /api/orders
     * Screen 1: "Orders" List (Active vs Past)
     * Query Param: ?type=active OR ?type=past
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->query('type', 'active'); // Default to active

        // Filter Statuses based on Tab
        if ($type === 'active') {
            $statuses = ['pending', 'processing', 'shipped']; // On the way
        } else {
            $statuses = ['delivered', 'cancelled']; // History
        }

        // Fetch Orders
        $orders = Order::where('user_id', $user->id)
            ->whereIn('status', $statuses)
            ->with(['items.product']) // Load products for preview images
            ->orderBy('created_at', 'desc')
            ->get();

        // Format for Mobile List
        $formatted = $orders->map(function($order) {
            $firstItem = $order->items->first();
            $count = $order->items->count();
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => ucfirst($order->status), // e.g., "Shipped"
                'total_amount' => $order->total_amount,
                // Logic: Show "Delivered on..." for past, "Estimated..." for active
                'date_text' => $order->status == 'delivered' 
                    ? 'Delivered on ' . ($order->delivery_date ? $order->delivery_date->format('M d') : 'N/A')
                    : 'Estimated ' . ($order->delivery_date ? $order->delivery_date->format('M d') : 'Tomorrow'),
                
                // Card Preview Data
                'preview_image' => $firstItem ? $firstItem->product->image_url : null,
                'preview_title' => $firstItem ? $firstItem->product->name : 'Order',
                'preview_subtitle' => $count > 1 ? "+ " . ($count - 1) . " more items" : ""
            ];
        });

        return response()->json(['data' => $formatted]);
    }

    /**
     * GET /api/orders/{id}
     * Screen 2: Single Order Detail (Tracking & Timeline)
     */
    public function show($id)
    {
        // Fetch Order with Items and Driver info
        $order = Order::with(['items.product', 'driver'])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Timeline Logic (Green Checkmarks)
        $timeline = [
            ['title' => 'Confirmed', 'completed' => true],
            ['title' => 'Packed',    'completed' => in_array($order->status, ['processing', 'shipped', 'delivered'])],
            ['title' => 'On the way','completed' => in_array($order->status, ['shipped', 'delivered'])],
            ['title' => 'Delivered', 'completed' => $order->status == 'delivered'],
        ];

        // Status Text logic
        $statusText = ucfirst($order->status);
        if ($order->status == 'shipped') $statusText = 'On the way';

        return response()->json([
            'order_number' => $order->order_number,
            'status_text' => $statusText,
            'arriving_time' => $order->estimated_arrival_time ?? 'Arriving today',
            'timeline' => $timeline,
            
            // Driver Information (From Relationship)
            'driver' => $order->driver ? [
                'name' => $order->driver->name,
                'vehicle' => $order->driver->vehicle_type,
                'phone' => $order->driver->phone,
                'avatar' => $order->driver->avatar_url,
            ] : null,

            // Item List
            'items' => $order->items->map(function($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image_url,
                    'price' => $item->price
                ];
            }),

            // Payment Summary Bottom Sheet
            'delivery_address' => $order->delivery_address,
            'summary' => [
                'subtotal' => $order->items->sum(fn($i) => $i->price * $i->quantity),
                'shipping_fee' => $order->delivery_fee,
                'voucher_discount' => -$order->discount_amount,
                'total' => $order->total_amount,
                'paid_with' => $order->payment_method == 'groceria_pay' ? 'Groceria Pay' : 'Card'
            ]
        ]);
    }

    /**
     * POST /api/orders/place
     * Handles Checkout, Payment, and Order Creation
     */
    public function store(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'guest_address' => 'nullable|required_without:address_id|array', 
            'payment_method' => 'required|in:cod,card,groceria_pay',
            'voucher_code' => 'nullable|exists:vouchers,code'
        ]);

        // 2. Retrieve Cart
        $cart = $this->getCart($request);
        if (!$cart || $cart->items()->count() == 0) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $cartItems = $cart->items()->with('product')->get();

        // 3. Calculate Financials
        $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);
        $shipping = 5.00;
        $discount = 0.00;

        if ($request->voucher_code) {
            $voucher = Voucher::where('code', $request->voucher_code)->first();
            if ($voucher && $voucher->expires_at > now()) {
                $discount = ($voucher->type == 'percent') 
                    ? $subtotal * ($voucher->value / 100) 
                    : $voucher->value;
            }
        }

        $total = max(0, ($subtotal + $shipping) - $discount);

        // 4. Resolve Address
        $user = $request->user('sanctum');
        $finalAddress = "";
        $guestInfo = null;

        if ($user && $request->address_id) {
            $addr = Address::find($request->address_id);
            $finalAddress = $addr->address_line;
        } else {
            $data = $request->guest_address;
            $finalAddress = $data['address_line'] ?? 'Unknown Address';
            $guestInfo = $data;
        }

        // 5. Transaction
        return DB::transaction(function () use ($request, $user, $cart, $cartItems, $total, $shipping, $discount, $finalAddress, $guestInfo) {

            $paymentStatus = 'pending';
            
            if ($request->payment_method === 'groceria_pay') {
                if (!$user) {
                    return response()->json(['message' => 'Guest cannot use Wallet'], 403);
                }
                if ($user->wallet_balance < $total) {
                    return response()->json(['message' => 'Insufficient wallet balance'], 400);
                }
                $user->decrement('wallet_balance', $total);
                $paymentStatus = 'paid';
            } elseif ($request->payment_method === 'card') {
                $paymentStatus = 'paid'; // Simulated success
            }

            // Create Order
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'guest_info' => $guestInfo,
                'order_number' => '#' . rand(10000000, 99999999),
                'status' => 'processing',
                'total_amount' => $total,
                'delivery_fee' => $shipping,
                'discount_amount' => $discount,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'delivery_address' => $finalAddress,
                'delivery_date' => now()->addDay(),
                'points_earned' => intval($total * 10)
            ]);

            // Move Items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
            }

            if ($user) {
                $user->increment('points', $order->points_earned);
            }

            $cart->items()->delete();

            return response()->json([
                'message' => 'Order placed successfully',
                'order_id' => $order->order_number,
                'total_paid' => $order->total_amount,
                'points_earned' => $order->points_earned,
                'estimated_delivery' => 'Tomorrow',
                'payment_status' => $paymentStatus
            ], 201);
        });

    }

    /**
     * POST /api/orders/{id}/tip
     * Screen 2: Add Tip to Driver
     */
    public function addTip(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.50',
            // In a real app, you would validate 'payment_method' here too
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->tip_amount > 0) {
            return response()->json(['message' => 'Tip already added'], 400);
        }

        // Update the order with the tip
        $order->tip_amount = $request->amount;
        $order->total_amount += $request->amount; // Add tip to total
        $order->save();

        return response()->json([
            'message' => 'Tip sent to driver', 
            'tip_amount' => $order->tip_amount,
            'new_total' => $order->total_amount
        ]);
    }

    /**
     * POST /api/orders/{id}/review
     * Screen 3: Rate Order & Driver
     */
    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'driver_rating' => 'required|integer|min:1|max:5',
            'experience_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $order = Order::find($id);
        $user = $request->user();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Prevent Duplicate Reviews (One review per order)
        $existing = \Illuminate\Support\Facades\DB::table('reviews')
            ->where('order_id', $id)
            ->exists();
            
        if ($existing) {
            return response()->json(['message' => 'You already reviewed this order'], 400);
        }

        // Insert Review
        DB::table('reviews')->insert([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'driver_id' => $order->driver_id, // Links rating to specific driver
            'driver_rating' => $request->driver_rating,
            'experience_rating' => $request->experience_rating,
            'comment' => $request->comment,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Review submitted successfully']);
    }
}