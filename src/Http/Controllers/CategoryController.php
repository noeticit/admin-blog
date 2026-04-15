<?php

namespace Noeticit\AdminBlog\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Noeticit\AdminBlog\Http\Requests\StoreCategoryRequest;
use Noeticit\AdminBlog\Http\Requests\UpdateCategoryRequest;
use Noeticit\AdminBlog\Models\Category;

class CategoryController
{
    public function index(): Response
    {
        $categories = Category::withCount('posts')->get();

        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Categories/Create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Category::create($validated);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
