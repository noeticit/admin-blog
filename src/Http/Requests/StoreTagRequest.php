<?php

namespace Noeticit\AdminBlog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:'.$prefix.'tags,slug'],
            'color' => ['nullable', 'string', 'max:7'],
        ];
    }
}
