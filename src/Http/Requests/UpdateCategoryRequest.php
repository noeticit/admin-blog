<?php

namespace Noeticit\AdminBlog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique($prefix.'categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:7'],
        ];
    }
}
