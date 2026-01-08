<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class ShopController extends Controller
{
    /**
     * 1. Get All Categories (The Grid)
     */
    public function categories()
    {
        $categories = Category::all();
        
        return response()->json([
            'message' => 'Categories fetched successfully',
            'data' => $categories
        ]);
    }

    /**
     * 2. Search Products (The Top Search Bar)
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([]);
        }

        // Search by product name OR category name
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhereHas('category', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->with('category') // Include category details in result
            ->get();

        return response()->json([
            'message' => 'Search results',
            'data' => $products
        ]);
    }

    /**
     * 3. Recently Viewed Products
     * Since guests don't have database history, the Mobile App sends us 
     * a list of IDs stored in the phone's local storage, and we return the details.
     */
    public function recent(Request $request)
    {
        // Expecting input: { "product_ids": [1, 5, 8] }
        $ids = $request->input('product_ids', []);

        if (empty($ids)) {
            return response()->json(['data' => []]);
        }

        $products = Product::whereIn('id', $ids)->get();

        return response()->json([
            'message' => 'Recently viewed products',
            'data' => $products
        ]);
    }

    /**
     * GET Single Category (Public)
     */
    public function showCategory($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json(['data' => $category]);
    }

    /**
     * POST Create Category (Admin only - Protected)
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories',
            'bg_color' => 'nullable|string',
            'image_url' => 'nullable|url'
        ]);

        $category = Category::create($request->all());

        return response()->json(['message' => 'Category created', 'data' => $category], 201);
    }

    /**
     * PUT Update Category (Admin only - Protected)
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->update($request->all());

        return response()->json(['message' => 'Category updated', 'data' => $category]);
    }

    /**
     * DELETE Category (Admin only - Protected)
     */
    public function destroyCategory($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }
    
}