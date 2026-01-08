<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class FavoriteController extends Controller
{
    // Screen: My Favorites
    public function index(Request $request)
    {
        // Return products that are favorited
        $products = $request->user()->favorites()->get();
        return response()->json($products);
    }

    // Toggle Favorite (Add/Remove)
    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $user = $request->user();
        $productId = $request->product_id;

        // Check if already exists
        if ($user->favorites()->where('product_id', $productId)->exists()) {
            $user->favorites()->detach($productId);
            return response()->json(['message' => 'Removed from favorites', 'status' => 'removed']);
        } else {
            $user->favorites()->attach($productId);
            return response()->json(['message' => 'Added to favorites', 'status' => 'added']);
        }
    }
}
