<?php

namespace Noeticit\AdminBlog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Noeticit\AdminBlog\Models\Tag
 */
class TagResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'posts_count' => $this->whenCounted('posts'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
