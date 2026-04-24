<?php

namespace App\Models;


use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Str;

class Article extends Model
{
    //
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    // Boot: auto slug + published_at
        protected static function boot(): void
    {
        parent::boot();

        // Auto-generate unique slug before creating
        static::creating(function (Article $article) {
            $article->slug = static::generateUniqueSlug($article->title);
        });

        // Re-generate slug if title changed on update
        static::updating(
            function (Article $article) {
            if ($article->isDirty('title')) {
                $article->slug = static::generateUniqueSlug($article->title, $article->id);
            }

            // Set published_at when status changes to published
            if ($article->isDirty('status')) {
                if ($article->status === 'published' && is_null($article->published_at)) {
                    $article->published_at = now();
                }

                // Clear published_at if reverted to draft
                if ($article->status === 'draft') {
                    $article->published_at = null;
                }
            }
        });
    }

        protected static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    // Scoopes

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
              ->whereNotNull('published_at');
    }

    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    // Relctionships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Helpers

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function excerpt(int $limit = 180): string
    {
        return Str::limit($this->content, $limit);
    }

}
