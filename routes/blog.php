<?php

use Illuminate\Support\Facades\Route;
use Noeticit\AdminBlog\Http\Controllers\CategoryController;
use Noeticit\AdminBlog\Http\Controllers\PostController;
use Noeticit\AdminBlog\Http\Controllers\TagController;

/*
|--------------------------------------------------------------------------
| Blog Admin Routes
|--------------------------------------------------------------------------
|
| These routes handle blog management in the admin panel.
|
*/

Route::middleware(['web', 'admin.auth'])
    ->prefix('admin/blog')
    ->name('admin.blog.')
    ->group(function () {
        // Posts
        Route::resource('posts', PostController::class);
        Route::post('posts/bulk-action', [PostController::class, 'bulkAction'])->name('posts.bulk-action');
        Route::post('posts/{post}/generate-meta', [PostController::class, 'generateMeta'])->name('posts.generate-meta');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Tags
        Route::resource('tags', TagController::class)->except(['show']);
    });
