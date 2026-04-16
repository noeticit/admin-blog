<?php

namespace Noeticit\AdminBlog\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Noeticit\AdminBlog\Http\Resources\PostResource;
use Noeticit\AdminBlog\Models\Post;
use Noeticit\AdminBlog\Services\PostService;

class PostApiController
{
    public function __construct(
        private readonly PostService $postService,
    ) {}

    /**
     * List blog posts with optional filtering.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::query()->with(['category', 'tags']);

        if ($request->query('status')) {
            $query->byStatus($request->query('status'));
        }

        if ($request->query('category')) {
            $query->byCategory($request->query('category'));
        }

        if ($request->query('search')) {
            $query->search($request->query('search'));
        }

        $posts = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return PostResource::collection($posts);
    }

    /**
     * Create a new blog post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'source_url' => ['nullable', 'string', 'url', 'max:2048'],
            'category_id' => ['nullable', 'integer', 'exists:'.config('blog.database.table_prefix').'categories,id'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'author_id' => ['nullable', 'integer'],
        ]);

        // Resolve category by name if category_id not provided
        if (empty($validated['category_id']) && ! empty($validated['category_name'])) {
            $category = \Noeticit\AdminBlog\Models\Category::firstOrCreate(
                ['slug' => Str::slug($validated['category_name'])],
                ['name' => $validated['category_name']],
            );
            $validated['category_id'] = $category->id;
        }
        unset($validated['category_name']);

        // Resolve tags by name — create if they don't exist
        $tagIds = [];
        if (! empty($validated['tags'])) {
            foreach ($validated['tags'] as $tagName) {
                $tag = \Noeticit\AdminBlog\Models\Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName],
                );
                $tagIds[] = $tag->id;
            }
            $validated['tags'] = $tagIds;
        }

        $post = $this->postService->create(
            $validated,
            $validated['author_id'] ?? null,
        );

        $post->load(['category', 'tags']);

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a single blog post.
     */
    public function show(Post $post): PostResource
    {
        $post->load(['category', 'tags', 'author']);

        return new PostResource($post);
    }

    /**
     * Update an existing blog post.
     */
    public function update(Request $request, Post $post): PostResource
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['sometimes', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'source_url' => ['nullable', 'string', 'url', 'max:2048'],
            'category_id' => ['nullable', 'integer'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ]);

        // Resolve category by name
        if (empty($validated['category_id']) && ! empty($validated['category_name'])) {
            $category = \Noeticit\AdminBlog\Models\Category::firstOrCreate(
                ['slug' => Str::slug($validated['category_name'])],
                ['name' => $validated['category_name']],
            );
            $validated['category_id'] = $category->id;
        }
        unset($validated['category_name']);

        // Resolve tags by name
        if (! empty($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = \Noeticit\AdminBlog\Models\Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName],
                );
                $tagIds[] = $tag->id;
            }
            $validated['tags'] = $tagIds;
        }

        $post = $this->postService->update($post, $validated);
        $post->load(['category', 'tags']);

        return new PostResource($post);
    }

    /**
     * Delete a blog post.
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->postService->delete($post);

        return response()->json(['message' => 'Post deleted.'], 200);
    }
}
