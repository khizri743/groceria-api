<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Voucher;

class CartController extends Controller
{
    /**
     * Helper: Find Cart for User OR Guest
     */
    private function getCart(Request $request)
    {
        // 1. Is User Logged In?
        $user = $request->user('sanctum'); // Check token manually
        
        if ($user) {
            // Find by User ID
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        // 2. Is it a Guest? (Check Header)
        $guestId = $request->header('X-Guest-ID');

        if (!$guestId) {
            abort(400, 'Missing X-Guest-ID header for guest checkout');
        }

        // Find by Guest ID
        return Cart::firstOrCreate(['guest_id' => $guestId]);
    }

    /**
     * GET /api/cart
     */
    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $items = $cart->items()->with('product')->get();

        $total = $items->sum(function($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'items' => $items,
            'subtotal' => round($total, 2)
        ]);
    }

    /**
     * POST /api/cart/add
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = $this->getCart($request);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json(['message' => 'Cart updated']);
    }

    /**
     * DELETE /api/cart/{itemId}
     */
    public function removeItem(Request $request, $itemId)
    {
        $cart = $this->getCart($request);
        
        CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->delete();

        return response()->json(['message' => 'Item removed']);
    }

    /**
     * POST /api/checkout/summary
     */
    public function checkoutSummary(Request $request)
    {
        $cart = $this->getCart($request);
        $items = $cart->items()->with('product')->get();

        // 1. Calculate Subtotal
        $subtotal = $items->sum(fn($item) => $item->quantity * $item->product->price);

        // 2. Shipping Fee
        // For guests, we assume standard $5 unless they send an address payload later
        $shippingFee = 5.00; 

        // 3. Voucher Logic
        $discountAmount = 0.00;
        $voucherMessage = null;

        if ($request->has('voucher_code')) {
            $voucher = Voucher::where('code', $request->voucher_code)->first();

            if ($voucher && $voucher->expires_at > now()) {
                if ($voucher->type == 'percent') {
                    $discountAmount = $subtotal * ($voucher->value / 100);
                } else {
                    $discountAmount = $voucher->value;
                }
                $voucherMessage = $voucher->description;
            } else {
                return response()->json(['message' => 'Invalid or expired voucher'], 422);
            }
        }

        // 4. Final Total
        $total = ($subtotal + $shippingFee) - $discountAmount;

        return response()->json([
            'subtotal' => round($subtotal, 2),
            'shipping_fee' => round($shippingFee, 2),
            'discount_amount' => round($discountAmount, 2),
            'voucher_applied' => $voucherMessage,
            'total' => max(0, round($total, 2)), 
            'items' => $items
        ]);
    }
}