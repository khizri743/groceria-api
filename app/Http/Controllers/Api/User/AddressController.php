<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->addresses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string', // Home, Work
            'address_line' => 'required|string',
        ]);

        $address = $request->user()->addresses()->create($request->all());

        return response()->json(['message' => 'Address added', 'data' => $address]);
    }

    public function destroy(Address $address)
    {
        // Security check: ensure address belongs to user
        if ($address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $address->delete();
        return response()->json(['message' => 'Address removed']);
    }
}