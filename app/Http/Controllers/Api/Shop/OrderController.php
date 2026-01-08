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
    // Helper: Find the Cart (Same logic as CartController)
    private function getCart(Request $request)
    {
        // 1. Try to get User Cart
        $user = $request->user('sanctum');
        if ($user) {
            return Cart::where('user_id', $user->id)->first();
        }

        // 2. Try to get Guest Cart
        $guestId = $request->header('X-Guest-ID');
        if ($guestId) {
            return Cart::where('guest_id', $guestId)->first();
        }

        return null;
    }

    /**
     * POST /api/orders/place
     * Handles Checkout, Payment, and Order Creation
     */
    public function store(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            // Address: Either an ID (for saved addresses) OR raw data (for guests)
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

        // 3. Calculate Financials (Subtotal, Discount, Total)
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

        // 4. Resolve Address & User
        $user = $request->user('sanctum');
        $finalAddress = "";
        $guestInfo = null;

        if ($user && $request->address_id) {
            // Logged in User selected a saved address
            $addr = Address::find($request->address_id);
            $finalAddress = $addr->address_line;
        } else {
            // Guest or User typing new address manually
            $data = $request->guest_address;
            $finalAddress = $data['address_line'] ?? 'Unknown Address';
            $guestInfo = $data; // Store full guest details (Name, Email, etc.)
        }

        // 5. START TRANSACTION (Safety First)
        return DB::transaction(function () use ($request, $user, $cart, $cartItems, $total, $shipping, $discount, $finalAddress, $guestInfo) {

            // A. Process Payment (Groceria Pay)
            $paymentStatus = 'pending';
            
            if ($request->payment_method === 'groceria_pay') {
                if (!$user) {
                    return response()->json(['message' => 'Guest cannot use Wallet'], 403);
                }
                if ($user->wallet_balance < $total) {
                    return response()->json(['message' => 'Insufficient wallet balance'], 400);
                }
                
                // Deduct Balance
                $user->decrement('wallet_balance', $total);
                $paymentStatus = 'paid';
            } elseif ($request->payment_method === 'card') {
                // In real app, you charge Stripe here. For demo, we assume success.
                $paymentStatus = 'paid';
            }

            // B. Create Order Record
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'guest_info' => $guestInfo,
                'order_number' => '#' . rand(10000000, 99999999), // Matches screenshot
                'status' => 'processing',
                'total_amount' => $total,
                'delivery_fee' => $shipping,
                'discount_amount' => $discount,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'delivery_address' => $finalAddress,
                'delivery_date' => now()->addDay(), // "Tomorrow"
                'points_earned' => intval($total * 10) // 10 points per dollar logic
            ]);

            // C. Move Items from Cart to Order Items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
            }

            // D. Award Points (If User)
            if ($user) {
                $user->increment('points', $order->points_earned);
            }

            // E. Empty the Cart
            $cart->items()->delete();

            // F. Return Response (Matching the Success Screen)
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
}