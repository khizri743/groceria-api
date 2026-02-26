<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * POST /api/wallet/topup
     * Add money to your wallet (Simulates a Bank Transfer)
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:1000'
        ]);

        $user = $request->user();
        
        // Add money
        $user->increment('wallet_balance', $request->amount);

        return response()->json([
            'message' => 'Wallet topped up successfully',
            'new_balance' => $user->wallet_balance
        ]);
    }

    /**
     * GET /api/wallet/history
     * See Balance and Points
     */
    public function show(Request $request)
    {
        return response()->json([
            'wallet_balance' => $request->user()->wallet_balance,
            'points' => $request->user()->points
        ]);
    }
}