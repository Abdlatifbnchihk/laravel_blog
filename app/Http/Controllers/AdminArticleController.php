<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminArticleController extends Controller
{
    // US5 — Dashboard: all articles (drafts + published)
    public function index(): View
    {
        $articles = Auth::user()
            ->articles()
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('admin.articles.index', compact('articles'));
    }

    // US6 — Show create form
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.create', compact('categories'));
    }

    // US6 — Store new article
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'min:5', 'max:255'],
            'content'        => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'exists:categories,id'],
            'status'      => ['required', 'in:draft,published'],
        ]);

        $article = Auth::user()->articles()->create([
            'title'       => $validated['title'],
            'content'        => $validated['content'],
            'category_id' => $validated['category_id'],
            'status'      => $validated['status'],
            // slug and published_at are handled by the Article model's boot()
        ]);

        $message = $article->isPublished()
            ? 'Article published successfully!'
            : 'Article saved as draft.';

        return redirect()
            ->route('admin.articles.index')
            ->with('success', $message);
    }

    // US7 — Show edit form
    public function edit(Article $article): View
    {
        $this->authorizeArticle($article);

        $categories = Category::orderBy('name')->get();

        return view('admin.articles.edit', compact('article', 'categories'));
    }

    // US7 — Update existing article
    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeArticle($article);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'min:5', 'max:255'],
            'content'        => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'exists:categories,id'],
            'status'      => ['required', 'in:draft,published'],
        ]);

        $article->update($validated);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article updated successfully!');
    }

    // US8 — Delete article
    public function destroy(Article $article): RedirectResponse
    {
        $this->authorizeArticle($article);

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article deleted.');
    }

    // ─── Private helper ───────────────────────────────────────────────────────

    private function authorizeArticle(Article $article): void
    {
        if ($article->user_id !== Auth::id()) {
            abort(403, 'This article does not belong to you.');
        }
    }
}