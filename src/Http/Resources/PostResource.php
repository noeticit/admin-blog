<?php

namespace Noeticit\AdminBlog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Noeticit\AdminBlog\Models\Post
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'updated_content_at' => $this->updated_content_at?->toISOString(),
            'source_url' => $this->source_url,

            // SEO
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'focus_keyword' => $this->focus_keyword,
            'canonical_url' => $this->canonical_url,

            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),

            // Analytics
            'reading_time' => $this->reading_time,
            'word_count' => $this->word_count,
            'views_count' => $this->views_count,
            'category_slug' => $this->category_slug,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
