<?php

namespace Noeticit\AdminBlog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
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
        $tagId = $this->route('tag')?->id ?? $this->route('tag');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique($prefix.'tags', 'slug')->ignore($tagId)],
            'color' => ['nullable', 'string', 'max:7'],
        ];
    }
}
