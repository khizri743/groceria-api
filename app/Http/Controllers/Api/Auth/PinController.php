<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    /**
     * POST /api/auth/verify-pin
     */
    public function verify(Request $request)
    {
        $request->validate(['pin' => 'required|digits:6']);

        $user = $request->user();

        // If user hasn't set a PIN yet
        if (!$user->pin_code) {
             // For demo, if they have no PIN, let's just say "123456" works
            if ($request->pin == '123456') return response()->json(['verified' => true]);
            
            return response()->json(['message' => 'PIN not set'], 400);
        }

        if (Hash::check($request->pin, $user->pin_code)) {
            return response()->json(['message' => 'Success', 'verified' => true]);
        }

        return response()->json(['message' => 'Incorrect PIN', 'verified' => false], 401);
    }
}