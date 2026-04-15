<?php

namespace Noeticit\AdminBlog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Noeticit\AdminBlog\Http\Requests\StorePostRequest;
use Noeticit\AdminBlog\Http\Requests\UpdatePostRequest;
use Noeticit\AdminBlog\Models\Category;
use Noeticit\AdminBlog\Models\Post;
use Noeticit\AdminBlog\Models\Tag;
use Noeticit\AdminBlog\Services\PostService;

class PostController
{
    public function __construct(
        private readonly PostService $postService,
    ) {}

    public function index(Request $request): Response
    {
        $query = Post::query()->with(['category', 'author', 'tags']);

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

        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy === 'views' ? 'views_count' : $sortBy, $sortDirection);

        $posts = $query->paginate(
            $request->get('per_page', config('blog.pagination.per_page', 15))
        )->withQueryString();

        $categories = Category::all();
        $tags = Tag::all();

        $stats = [
            'total' => Post::count(),
            'published' => Post::where('status', 'published')->count(),
            'draft' => Post::where('status', 'draft')->count(),
            'archived' => Post::where('status', 'archived')->count(),
        ];

        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Index', [
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

        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Create', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $authGuard = config('blog.author.guard', 'web');

        $this->postService->create(
            $request->validated(),
            Auth::guard($authGuard)->id()
        );

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function show(Post $post): Response
    {
        $post->load(['category', 'author', 'tags']);

        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Show', [
            'post' => $post,
        ]);
    }

    public function edit(Post $post): Response
    {
        $post->load(['category', 'author', 'tags']);

        $categories = Category::all();
        $tags = Tag::all();

        $postData = $post->toArray();
        $postData['tags'] = $post->tags->pluck('id')->toArray();

        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Edit', [
            'post' => $postData,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->postService->update($post, $request->validated());

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->postService->delete($post);

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Bulk actions on multiple posts.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:delete,publish,draft,archive'],
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:'.config('blog.database.table_prefix').'posts,id'],
        ]);

        $message = $this->postService->bulkAction($request->action, $request->ids);

        return redirect()->route('admin.blog.posts.index')
            ->with('success', $message);
    }

    /**
     * Generate SEO meta tags using AI.
     */
    public function generateMeta(Request $request, Post $post): JsonResponse
    {
        $aiService = config('blog.ai.service');
        if (! $aiService || ! config('blog.features.ai_suggestions', false)) {
            return response()->json(['error' => 'AI service is not configured'], 400);
        }

        try {
            $ai = app($aiService);
            $meta = $ai->generateSEOMeta($post->content);

            return response()->json($meta);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate SEO meta tags from content (no existing post required).
     */
    public function generateMetaFromContent(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $aiService = config('blog.ai.service');
        if (! $aiService || ! config('blog.features.ai_suggestions', false)) {
            return response()->json(['error' => 'AI service is not configured'], 400);
        }

        $title = $request->input('title', '');
        $content = $request->input('content', '');

        if (empty($title) && empty($content)) {
            return response()->json(['error' => 'Please provide title or content'], 400);
        }

        try {
            $ai = app($aiService);
            $fullContent = $title ? "Title: {$title}\n\n{$content}" : $content;
            $meta = $ai->generateSEOMeta($fullContent);

            return response()->json($meta);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload an image for blog posts.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        try {
            $file = $request->file('image');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $disk = config('blog.uploads.disk', 'public');
            $path = $file->storeAs(config('blog.uploads.path', 'blog-images'), $filename, $disk);

            return response()->json([
                'url' => Storage::disk($disk)->url($path),
                'path' => $path,
                'filename' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
