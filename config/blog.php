<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blog Configuration
    |--------------------------------------------------------------------------
    */

    'routes' => [
        'prefix' => 'admin/blog',
        'middleware' => ['web', 'admin.auth'],
    ],

    'database' => [
        'table_prefix' => env('BLOG_TABLE_PREFIX', 'blog_'),
    ],

    'features' => [
        'seo' => true,
        'categories' => true,
        'tags' => true,
        'featured_images' => true,
        'ai_suggestions' => env('BLOG_AI_ENABLED', true),
    ],

    'pagination' => [
        'per_page' => 15,
    ],
];
