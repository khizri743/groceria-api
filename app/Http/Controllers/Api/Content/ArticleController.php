<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * GET /api/articles
     * Filters: ?search=keyword OR ?tag=Nutrition
     */
    public function index(Request $request)
    {
        $query = Article::query();

        // 1. Search Filter
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // 2. Tag Filter (Tabs: All, Nutrition, Recipes...)
        if ($request->has('tag') && $request->tag != 'All') {
            $query->whereJsonContains('tags', $request->tag);
        }

        $articles = $query->orderBy('published_date', 'desc')->get();

        return response()->json([
            'message' => 'Articles retrieved',
            'data' => $articles
        ]);
    }

    /**
     * GET /api/articles/{id}
     * Article Detail + "Read Next"
     */
    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        // Fetch "Read Next" (Random articles excluding current one)
        $readNext = Article::where('id', '!=', $id)
            ->inRandomOrder()
            ->take(3)
            ->get();

        return response()->json([
            'data' => $article,
            'read_next' => $readNext
        ]);
    }
}