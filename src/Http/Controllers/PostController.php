<?php

namespace Noeticit\AdminBlog\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Noeticit\Admin\Services\AI\AIServiceInterface;
use Noeticit\AdminBlog\Models\Category;
use Noeticit\AdminBlog\Models\Post;
use Noeticit\AdminBlog\Models\Tag;

class PostController
{
    public function index(Request $request): Response
    {
        $query = Post::query()->with(['category', 'author', 'tags']);

        // Apply filters
        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->category) {
            $query->byCategory($request->category);
        }

        if ($request->status) {
            $query->byStatus($request->status);
        }

        if ($request->author) {
            $query->byAuthor($request->author);
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $query->orderBy($sortBy === 'views' ? 'views_count' : $sortBy, $sortDirection);

        $posts = $query->paginate($request->get('per_page', 15))->withQueryString();

        // Get filter options
        $categories = Category::all();
        $tags = Tag::all();

        // Stats
        $stats = [
            'total' => Post::count(),
            'published' => Post::where('status', 'published')->count(),
            'draft' => Post::where('status', 'draft')->count(),
            'archived' => Post::where('status', 'archived')->count(),
        ];

        return Inertia::render('Admin/Blog/Posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'stats' => $stats,
            'filters' => $request->only(['search', 'category', 'status', 'author', 'sort', 'direction', 'per_page']),
        ]);
    }

    public function create(): Response
    {
        $categories = Category::all();
        $tags = Tag::all();

        return Inertia::render('Admin/Blog/Posts/Create', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'nullable|max:255|unique:'.config('blog.database.table_prefix').'posts',
            'content' => 'required|string',
            'body_blocks' => 'nullable|array',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'category_id' => 'nullable|exists:'.config('blog.database.table_prefix').'categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:'.config('blog.database.table_prefix').'tags,id',

            // SEO fields
            'meta_title' => 'nullable|max:60',
            'meta_description' => 'nullable|max:160',
            'meta_keywords' => 'nullable|array',
            'focus_keyword' => 'nullable|string',
            'og_title' => 'nullable|string',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string',
            'canonical_url' => 'nullable|url',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set author
        $validated['author_id'] = Auth::guard('admin')->id();

        // Calculate reading time and word count
        $wordCount = str_word_count(strip_tags($validated['content']));
        $validated['word_count'] = $wordCount;
        $validated['reading_time'] = max(1, ceil($wordCount / 200)); // 200 words per minute

        // Create post
        $post = Post::create($validated);

        // Sync tags
        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function show(Post $post): Response
    {
        $post->load(['category', 'author', 'tags']);

        return Inertia::render('Admin/Blog/Posts/Show', [
            'post' => $post,
        ]);
    }

    public function edit(Post $post): Response
    {
        $post->load(['category', 'author', 'tags']);

        $categories = Category::all();
        $tags = Tag::all();

        return Inertia::render('Admin/Blog/Posts/Edit', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => [
                'nullable',
                'max:255',
                Rule::unique(config('blog.database.table_prefix').'posts')->ignore($post->id),
            ],
            'content' => 'required|string',
            'body_blocks' => 'nullable|array',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'category_id' => 'nullable|exists:'.config('blog.database.table_prefix').'categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:'.config('blog.database.table_prefix').'tags,id',

            // SEO fields
            'meta_title' => 'nullable|max:60',
            'meta_description' => 'nullable|max:160',
            'meta_keywords' => 'nullable|array',
            'focus_keyword' => 'nullable|string',
            'og_title' => 'nullable|string',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string',
            'canonical_url' => 'nullable|url',
        ]);

        // Update slug if changed
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Recalculate reading time and word count
        $wordCount = str_word_count(strip_tags($validated['content']));
        $validated['word_count'] = $wordCount;
        $validated['reading_time'] = max(1, ceil($wordCount / 200));

        // Update post
        $post->update($validated);

        // Sync tags
        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Generate SEO meta tags using AI.
     */
    public function generateMeta(Request $request, Post $post)
    {
        if (!config('admin.ai.enabled')) {
            return response()->json(['error' => 'AI service is disabled'], 400);
        }

        try {
            $ai = app(AIServiceInterface::class);
            $meta = $ai->generateSEOMeta($post->content);

            return response()->json($meta);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,publish,draft,archive',
            'ids' => 'required|array',
            'ids.*' => 'exists:'.config('blog.database.table_prefix').'posts,id',
        ]);

        $posts = Post::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'delete':
                $posts->delete();
                $message = 'Posts deleted successfully.';
                break;
            case 'publish':
                $posts->update(['status' => 'published', 'published_at' => now()]);
                $message = 'Posts published successfully.';
                break;
            case 'draft':
                $posts->update(['status' => 'draft']);
                $message = 'Posts moved to draft.';
                break;
            case 'archive':
                $posts->update(['status' => 'archived']);
                $message = 'Posts archived successfully.';
                break;
        }

        return redirect()->route('admin.blog.posts.index')
            ->with('success', $message);
    }
}
