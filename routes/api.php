<?php

use Illuminate\Support\Facades\Route;
use Noeticit\AdminBlog\Http\Controllers\Api\PostApiController;
use Noeticit\AdminBlog\Http\Middleware\ValidateBlogApiKey;

/*
|--------------------------------------------------------------------------
| Blog API Routes
|--------------------------------------------------------------------------
|
| External API for creating blog posts remotely (e.g. from content agents).
| Protected by API key via X-Blog-Api-Key or X-API-Key header.
|
| Enable in .env: BLOG_API_ENABLED=true, BLOG_API_KEY=your-secret-key
|
*/

Route::middleware(array_merge(
    config('blog.api.middleware', ['api']),
    [ValidateBlogApiKey::class]
))
    ->prefix(config('blog.api.prefix', 'api/blog'))
    ->name('blog.api.')
    ->group(function () {
        Route::get('posts', [PostApiController::class, 'index'])->name('posts.index');
        Route::post('posts', [PostApiController::class, 'store'])->name('posts.store');
        Route::get('posts/{post}', [PostApiController::class, 'show'])->name('posts.show');
        Route::put('posts/{post}', [PostApiController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post}', [PostApiController::class, 'destroy'])->name('posts.destroy');
    });
