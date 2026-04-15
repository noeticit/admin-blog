<?php

namespace Noeticit\AdminBlog\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Noeticit\AdminBlog\Http\Requests\StoreTagRequest;
use Noeticit\AdminBlog\Http\Requests\UpdateTagRequest;
use Noeticit\AdminBlog\Models\Tag;

class TagController
{
    public function index(): Response
    {
        $tags = Tag::withCount('posts')->get();

        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Tags/Create');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Tag::create($validated);

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag): Response
    {
        return Inertia::render(config('blog.inertia.page_prefix', 'Admin/Blog').'/Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $tag->update($validated);

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
