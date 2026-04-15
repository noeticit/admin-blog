<?php

use Illuminate\Support\Facades\Route;
use Noeticit\AdminBlog\Http\Controllers\CategoryController;
use Noeticit\AdminBlog\Http\Controllers\PostController;
use Noeticit\AdminBlog\Http\Controllers\TagController;

Route::middleware(config('blog.routes.middleware', ['web', 'auth']))
    ->prefix(config('blog.routes.prefix', 'admin/blog'))
    ->name('admin.blog.')
    ->group(function () {
        // Posts
        Route::resource('posts', PostController::class);
        Route::post('posts/bulk-action', [PostController::class, 'bulkAction'])->name('posts.bulk-action');
        Route::post('posts/{post}/generate-meta', [PostController::class, 'generateMeta'])->name('posts.generate-meta');
        Route::post('posts/generate-meta', [PostController::class, 'generateMetaFromContent'])->name('posts.generate-meta-content');
        Route::post('upload-image', [PostController::class, 'uploadImage'])->name('upload-image');

        // Categories
        if (config('blog.features.categories', true)) {
            Route::resource('categories', CategoryController::class)->except(['show']);
        }

        // Tags
        if (config('blog.features.tags', true)) {
            Route::resource('tags', TagController::class)->except(['show']);
        }
    });
