<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    /**
     * GET /api/payment-methods
     * Screen: "Select Payment Method"
     * Lists saved cards + Groceria Pay Balance
     */
    public function index(Request $request)
    {
        $cards = $request->user()->paymentMethods;

        return response()->json([
            'groceria_pay_balance' => $request->user()->wallet_balance, // "Groceria Pay"
            'saved_cards' => $cards
        ]);
    }

    /**
     * POST /api/payment-methods
     * Screen: "Add Card" Modal
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_holder_name' => 'required|string',
            'card_number' => 'required|digits_between:13,19', 
            'expiry_date' => 'required|string', // MM/YY
            'cvv' => 'required|digits:3' 
        ]);

        // SIMULATION: Detect brand and fake the save
        // In a real app, you send this to Stripe, not your DB.
        $brand = str_starts_with($request->card_number, '4') ? 'Visa' : 'Mastercard';
        $lastFour = substr($request->card_number, -4);

        $card = PaymentMethod::create([
            'user_id' => $request->user()->id,
            'card_holder_name' => $request->card_holder_name,
            'brand' => $brand,
            'last_four' => $lastFour,
            'expiry_date' => $request->expiry_date,
            'is_default' => true
        ]);

        return response()->json(['message' => 'Card saved', 'data' => $card]);
    }
}