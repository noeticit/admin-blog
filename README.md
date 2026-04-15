# Admin Blog Package

Reusable blog management package for Laravel with Inertia.js (Vue 3). Provides a complete admin blog system with categories, tags, advanced SEO, AI meta generation, bulk actions, and configurable architecture.

## Features

- **Posts CRUD** with rich text editor, featured images, and draft/published/archived statuses
- **Categories & Tags** with color coding and post counts
- **Advanced SEO** - meta title/description, Open Graph, Twitter cards, canonical URLs, robots directives, focus keywords, schema markup, table of contents
- **AI Meta Generation** - plug in your own AI service to auto-generate SEO metadata
- **Bulk Actions** - publish, draft, archive, or delete multiple posts
- **Image Uploads** - configurable disk and path
- **View Tracking** - built-in views counter
- **Reading Metrics** - auto-calculated reading time and word count
- **Source URL Tracking** - track where content originated (e.g. Google Docs)
- **Soft Deletes** - safe deletion with recovery
- **Configurable** - table prefix, author model, middleware, Inertia page prefix, feature flags
- **Publishable Assets** - Vue pages and components published to your project for full customization
- **Standalone or with Admin Core** - works independently or integrates with `noeticit/admin-core` for menu/permissions

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Inertia.js v1 or v2 (Vue 3)

## Installation

### 1. Add repository (if not on Packagist)

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/noeticit/admin-blog"
        }
    ]
}
```

### 2. Install

```bash
composer require noeticit/admin-blog
```

### 3. Publish assets

```bash
# Publish everything (config + Vue pages + components)
php artisan vendor:publish --tag=blog-all

# Or publish selectively
php artisan vendor:publish --tag=blog-config
php artisan vendor:publish --tag=blog-pages
php artisan vendor:publish --tag=blog-components
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Build frontend

```bash
npm run build
```

## Configuration

Publish the config file and customize `config/blog.php`:

```php
return [
    'routes' => [
        'prefix' => 'admin/blog',
        'middleware' => ['web', 'auth'],
    ],

    'database' => [
        'table_prefix' => 'blog_',
    ],

    'author' => [
        'model' => 'App\\Models\\User',
        'table' => 'users',
        'guard' => 'web',
    ],

    'features' => [
        'seo' => true,
        'categories' => true,
        'tags' => true,
        'featured_images' => true,
        'ai_suggestions' => false,
    ],

    'ai' => [
        'service' => null,  // Your AI service class
    ],

    'inertia' => [
        'page_prefix' => 'Admin/Blog',
    ],

    'uploads' => [
        'disk' => 'public',
        'path' => 'blog-images',
    ],
];
```

## Extending

### Custom PostService

You can extend `PostService` for project-specific logic:

```php
use Noeticit\AdminBlog\Services\PostService;

class MyPostService extends PostService
{
    public function createFromGoogleDocs(string $url, ?int $authorId = null): Post
    {
        $content = $this->fetchGoogleDocsContent($url);
        return $this->create([
            'title' => 'From Google Docs',
            'content' => $content,
            'source_url' => $url,
        ], $authorId);
    }
}
```

### AI Meta Generation

Implement a service with a `generateSEOMeta(string $content): array` method and bind it in config:

```php
'ai' => [
    'service' => App\Services\AIMetaService::class,
],
```

## Routes

| Method | URI | Name |
|--------|-----|------|
| GET | /admin/blog/posts | admin.blog.posts.index |
| GET | /admin/blog/posts/create | admin.blog.posts.create |
| POST | /admin/blog/posts | admin.blog.posts.store |
| GET | /admin/blog/posts/{post} | admin.blog.posts.show |
| GET | /admin/blog/posts/{post}/edit | admin.blog.posts.edit |
| PUT | /admin/blog/posts/{post} | admin.blog.posts.update |
| DELETE | /admin/blog/posts/{post} | admin.blog.posts.destroy |
| POST | /admin/blog/posts/bulk-action | admin.blog.posts.bulk-action |
| POST | /admin/blog/posts/{post}/generate-meta | admin.blog.posts.generate-meta |
| POST | /admin/blog/posts/generate-meta | admin.blog.posts.generate-meta-content |
| POST | /admin/blog/upload-image | admin.blog.upload-image |
| Resource | /admin/blog/categories | admin.blog.categories.* |
| Resource | /admin/blog/tags | admin.blog.tags.* |

## License

Proprietary - Noetic IT Services.
