<?php

namespace Noeticit\AdminBlog\Services;

use Illuminate\Support\Str;
use Noeticit\AdminBlog\Models\Post;

class PostService
{
    /**
     * Create a post from validated data.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, ?int $authorId = null): Post
    {
        $validated = $this->preparePostData($validated, $authorId);

        $post = Post::create($validated);

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return $post;
    }

    /**
     * Update a post from validated data.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Post $post, array $validated): Post
    {
        // Recalculate reading metrics if content changed
        if (isset($validated['content'])) {
            $metrics = Post::calculateReadingMetrics($validated['content']);
            $validated = array_merge($validated, $metrics);
        }

        // Regenerate slug if empty
        if (empty($validated['slug'])) {
            $validated['slug'] = Post::generateUniqueSlug($validated['title'], $post->id);
        }

        // Track content updates
        if (isset($validated['content']) && $validated['content'] !== $post->content) {
            $validated['updated_content_at'] = now();
        }

        $post->update($validated);

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return $post->fresh();
    }

    /**
     * Delete a post (soft delete).
     */
    public function delete(Post $post): void
    {
        $post->delete();
    }

    /**
     * Bulk action on multiple posts.
     *
     * @param  array<int>  $ids
     */
    public function bulkAction(string $action, array $ids): string
    {
        $posts = Post::whereIn('id', $ids);

        return match ($action) {
            'delete' => tap('Posts deleted successfully.', fn () => $posts->delete()),
            'publish' => tap('Posts published successfully.', fn () => $posts->update([
                'status' => 'published',
                'published_at' => now(),
            ])),
            'draft' => tap('Posts moved to draft.', fn () => $posts->update(['status' => 'draft'])),
            'archive' => tap('Posts archived successfully.', fn () => $posts->update(['status' => 'archived'])),
            default => 'Unknown action.',
        };
    }

    /**
     * Prepare post data for creation.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function preparePostData(array $validated, ?int $authorId = null): array
    {
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Post::generateUniqueSlug($validated['title']);
        }

        // Set author
        if ($authorId && empty($validated['author_id'])) {
            $validated['author_id'] = $authorId;
        }

        // Calculate reading metrics
        if (! empty($validated['content'])) {
            $metrics = Post::calculateReadingMetrics($validated['content']);
            $validated = array_merge($validated, $metrics);
        }

        // Auto-generate excerpt if not provided
        if (empty($validated['excerpt']) && ! empty($validated['content'])) {
            $validated['excerpt'] = Str::limit(
                strip_tags(preg_replace('/^#{1,6}\s+/m', '', $validated['content'])),
                300
            );
        }

        // Set published_at for published posts
        if (($validated['status'] ?? 'draft') === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        return $validated;
    }
}
