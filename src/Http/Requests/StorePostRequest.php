<?php

namespace Noeticit\AdminBlog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $prefix = config('blog.database.table_prefix');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:'.$prefix.'posts,slug'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'body_blocks' => ['nullable', 'array'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'source_url' => ['nullable', 'string', 'url', 'max:2048'],
            'category_id' => ['nullable', 'exists:'.$prefix.'categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:'.$prefix.'tags,id'],

            // SEO fields
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'meta_keywords' => ['nullable', 'array'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:300'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'robots_index' => ['nullable', 'boolean'],
            'robots_follow' => ['nullable', 'boolean'],
            'show_toc' => ['nullable', 'boolean'],
        ];
    }
}
