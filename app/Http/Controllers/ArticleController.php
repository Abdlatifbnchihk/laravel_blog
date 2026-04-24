<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    // US1 + US3 — Public article list with optional category filter
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $articles = Article::with('category')
            ->published()
            ->when(
                $request->category,
                fn ($query, $slug) =>
                    $query->whereHas('category', fn ($q) => $q->where('slug', $slug))
            )
            ->latest('published_at')
            ->paginate(10);

        $activeCategory = $request->category;

        return view('articles.index', compact('articles', 'categories', 'activeCategory'));
    }

    // US2 — Public article detail
    public function show(string $slug): View
    {
        $article = Article::with('category')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('articles.show', compact('article'));
    }
}