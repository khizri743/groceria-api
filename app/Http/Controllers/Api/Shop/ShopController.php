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
     * 2. Search Products (Global Search)
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([]);
        }

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhereHas('category', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->with('category')
            ->get();

        return response()->json([
            'message' => 'Search results',
            'data' => $products
        ]);
    }

    /**
     * 3. Get Products by Category (With Filters & Sorting)
     * URL: /api/categories/{id}/products?sort=price_low&lifestyle=vegan
     */
    public function getCategoryProducts(Request $request, $categoryId)
    {
        // 1. Start with products in this category
        $query = Product::where('category_id', $categoryId);

        // 2. Search Filter (Inside the category)
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 3. Lifestyle/Tag Filter (e.g. "vegan")
        if ($request->has('lifestyle')) {
            $tag = $request->lifestyle;
            // JSON Search
            $query->whereJsonContains('tags', $tag);
        }

        // 4. Sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        $products = $query->get();

        return response()->json([
            'category_id' => $categoryId,
            'count' => $products->count(),
            'data' => $products
        ]);
    }

    // GET All Products
    public function products()
    {
        // We use 'paginate(20)' so the app doesn't crash loading 1000 items at once.
        // We use 'with('category')' so the frontend knows which category the product belongs to.
        $products = Product::with('category')->paginate(20);

        return response()->json([
            'message' => 'All products fetched',
            'data' => $products
        ]);
    }

    /**
     * 4. Product Detail (With Freshness Logic)
     */
    public function showProduct($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Dynamic Harvest Badge (Logic for "Picked yesterday")
        $harvestBadge = null;
        if ($product->harvest_date) {
            $diff = $product->harvest_date->diffInDays(now());
            if ($diff == 0) $harvestBadge = "Picked today";
            elseif ($diff == 1) $harvestBadge = "Picked yesterday";
            else $harvestBadge = "Harvested $diff days ago";
        }

        // Similar Products (Same category, exclude self)
        $similar = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $id)
            ->take(3)
            ->get();

        return response()->json([
            'data' => $product,
            'freshness_badge' => $harvestBadge, 
            'similar_products' => $similar
        ]);
    }

    /**
     * 5. Compare Products
     * Body: { "ids": [1, 2] }
     */
    public function compare(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:2'
        ]);

        $products = Product::whereIn('id', $request->ids)->get();

        return response()->json([
            'message' => 'Comparison data retrieved',
            'data' => $products
        ]);
    }

    /**
     * 6. Recently Viewed Products
     */
    public function recent(Request $request)
    {
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
     * 7. GET Single Category (Basic Info)
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
     * ADMIN: Create Category
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
     * ADMIN: Update Category
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
     * ADMIN: Delete Category
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

    /**
     * ADMIN: Create Product
     */
    public function storeProduct(Request $request)
    {
        // Basic validation (You can add more specific rules for JSON fields if needed)
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'unit' => 'required|string', // e.g., 'kg', 'pack'
            'image_url' => 'nullable|url',
            'tags' => 'nullable|array',
            'gallery_images' => 'nullable|array',
            'specifications' => 'nullable|array'
        ]);

        // Create using all inputs (The model casts will handle the JSON arrays automatically)
        $product = Product::create($request->all());

        return response()->json(['message' => 'Product created', 'data' => $product], 201);
    }

    /**
     * ADMIN: Update Product
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Update with whatever data is sent
        $product->update($request->all());

        return response()->json(['message' => 'Product updated', 'data' => $product]);
    }

    /**
     * ADMIN: Delete Product
     */
    public function destroyProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}