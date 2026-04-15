<?php

namespace Noeticit\AdminBlog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('blog.database.table_prefix').'posts';
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'body_blocks',
        'featured_image',
        'status',
        'published_at',
        'updated_content_at',
        'source_url',

        // SEO Fields
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical_url',
        'focus_keyword',
        'secondary_keywords',
        'schema_markup',

        // Relationships
        'category_id',
        'author_id',

        // Computed
        'reading_time',
        'word_count',
        'views_count',

        // Robots
        'robots_index',
        'robots_follow',

        // Table of Contents
        'show_toc',
        'toc_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'updated_content_at' => 'datetime',
            'views_count' => 'integer',
            'reading_time' => 'integer',
            'word_count' => 'integer',
            'meta_keywords' => 'array',
            'secondary_keywords' => 'array',
            'schema_markup' => 'array',
            'body_blocks' => 'array',
            'toc_data' => 'array',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'show_toc' => 'boolean',
        ];
    }

    protected $attributes = [
        'status' => 'draft',
        'views_count' => 0,
        'robots_index' => true,
        'robots_follow' => true,
        'show_toc' => true,
    ];

    protected $appends = [
        'category_slug',
    ];

    /**
     * Get the category for the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the author for the post.
     * Uses the configurable author model so each project can use its own User model.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('blog.author.model', 'App\\Models\\User'), 'author_id');
    }

    /**
     * Get the tags for the post.
     */
    public function tags(): BelongsToMany
    {
        $prefix = config('blog.database.table_prefix');

        return $this->belongsToMany(Tag::class, $prefix.'post_tag', 'post_id', 'tag_id');
    }

    /**
     * Scope to get only published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to search posts.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
                ->orWhere('meta_description', 'like', "%{$search}%")
                ->orWhereHas('category', function ($categoryQuery) use ($search) {
                    $categoryQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('tags', function ($tagQuery) use ($search) {
                    $tagQuery->where('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Scope to filter by category slug.
     */
    public function scopeByCategory(Builder $query, ?string $categorySlug): Builder
    {
        if (! $categorySlug) {
            return $query;
        }

        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    /**
     * Scope to filter by tag slug.
     */
    public function scopeByTag(Builder $query, ?string $tagSlug): Builder
    {
        if (! $tagSlug) {
            return $query;
        }

        return $query->whereHas('tags', function ($q) use ($tagSlug) {
            $q->where('slug', $tagSlug);
        });
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope to filter by author.
     */
    public function scopeByAuthor(Builder $query, ?int $authorId): Builder
    {
        if (! $authorId) {
            return $query;
        }

        return $query->where('author_id', $authorId);
    }

    /**
     * Scope to filter by source URL.
     */
    public function scopeBySourceUrl(Builder $query, string $sourceUrl): Builder
    {
        return $query->where('source_url', $sourceUrl);
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Check if post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * Get the category slug, defaulting to 'general'.
     */
    public function getCategorySlugAttribute(): string
    {
        return $this->category?->slug ?? 'general';
    }

    /**
     * Get robots meta content.
     */
    public function getRobotsMetaContent(): string
    {
        $robots = [];

        if (! $this->robots_index) {
            $robots[] = 'noindex';
        }
        if (! $this->robots_follow) {
            $robots[] = 'nofollow';
        }

        return ! empty($robots) ? implode(', ', $robots) : 'index, follow';
    }

    /**
     * Check if SEO is optimized.
     */
    public function isSEOOptimized(): bool
    {
        return ! empty($this->meta_title)
            && ! empty($this->meta_description)
            && ! empty($this->focus_keyword)
            && ! empty($this->og_title)
            && ! empty($this->og_description);
    }

    /**
     * Generate a unique slug from a title.
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
            $query = static::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    /**
     * Calculate reading time and word count from content.
     *
     * @return array{reading_time: int, word_count: int}
     */
    public static function calculateReadingMetrics(string $content): array
    {
        $wordCount = str_word_count(strip_tags($content));

        return [
            'reading_time' => max(1, (int) ceil($wordCount / 200)),
            'word_count' => $wordCount,
        ];
    }
}
