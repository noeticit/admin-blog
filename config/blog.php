<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blog Configuration
    |--------------------------------------------------------------------------
    */

    'routes' => [
        'prefix' => 'admin/blog',
        'middleware' => ['web', 'auth'],
    ],

    'database' => [
        'table_prefix' => env('BLOG_TABLE_PREFIX', 'blog_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Author Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which model and table represent the blog authors.
    | This allows each project to use its own User model.
    |
    */

    'author' => [
        'model' => env('BLOG_AUTHOR_MODEL', 'App\\Models\\User'),
        'table' => env('BLOG_AUTHOR_TABLE', 'users'),
        'guard' => env('BLOG_AUTHOR_GUARD', 'web'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'seo' => true,
        'categories' => true,
        'tags' => true,
        'featured_images' => true,
        'ai_suggestions' => env('BLOG_AI_ENABLED', false),
        'soft_deletes' => true,
        'views_tracking' => true,
        'table_of_contents' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Service
    |--------------------------------------------------------------------------
    |
    | Bind your own AI service class that implements a generateSEOMeta() method.
    | Set to null to disable AI features.
    |
    */

    'ai' => [
        'service' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Inertia Page Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where Inertia looks for the blog Vue pages.
    | Pages are published to your resources/js/Pages directory.
    |
    */

    'inertia' => [
        'page_prefix' => 'Admin/Blog',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'disk' => 'public',
        'path' => 'blog-images',
        'max_size' => 5120, // KB
    ],
];
