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
    | In-article CTAs
    |--------------------------------------------------------------------------
    |
    | Calls-to-action authors can drop into the post body from the editor's
    | "Insert CTA" menu. Items are grouped under category headings (the menu
    | shows one heading per group). Each item's `shortcode` is inserted
    | verbatim into the content — your front-end is responsible for rendering
    | it (e.g. a Vue shortcode registry on the public post page).
    |
    | Override this in your published config/blog.php to match the shortcodes
    | your site knows how to render. Set to an empty array to hide the menu.
    |
    */

    'cta' => [
        'groups' => [
            [
                'label' => 'Engagement',
                'items' => [
                    [
                        'label' => 'Build an app (quote)',
                        'description' => 'Prompt readers to request a project quote',
                        'shortcode' => '[cta:quote]',
                    ],
                    [
                        'label' => 'Newsletter signup',
                        'description' => 'Inline email capture',
                        'shortcode' => '[cta:newsletter]',
                    ],
                ],
            ],
            [
                'label' => 'Resources',
                'items' => [
                    [
                        'label' => 'Download / lead magnet',
                        'description' => 'Promote a whitepaper or gated resource',
                        'shortcode' => '[cta:download title="The AI Readiness Checklist" href="/whitepapers/ai-readiness"]',
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'disk' => env('BLOG_UPLOAD_DISK', 'public'),
        'path' => env('BLOG_UPLOAD_PATH', 'blog-images'),
        'max_size' => 5120, // KB
    ],

    /*
    |--------------------------------------------------------------------------
    | External API
    |--------------------------------------------------------------------------
    |
    | Enable an API endpoint so external services (e.g. content agents)
    | can create blog posts on this project remotely.
    |
    | Generate a key: php artisan blog:generate-key
    | Or set any secure random string in BLOG_API_KEY.
    |
    */

    'api' => [
        'enabled' => env('BLOG_API_ENABLED', false),
        'key' => env('BLOG_API_KEY'),
        'prefix' => 'api/blog',
        'middleware' => ['api'],
        'rate_limit' => 60,
    ],
];
