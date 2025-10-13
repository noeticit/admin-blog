# Noetic Admin Blog Module

Blog management module for Noetic Admin Core. Provides complete blog CRUD functionality with SEO optimization, categories, tags, and AI-powered content assistance.

## Features

- ✅ **Complete CRUD** - Create, read, update, delete blog posts
- ✅ **Categories & Tags** - Organize posts with categories and tags
- ✅ **SEO Optimization** - Meta tags, OG tags, Twitter cards, schema markup
- ✅ **AI Integration** - Auto-generate SEO meta tags using admin-core AI service
- ✅ **Block-based Content** - Support for structured content blocks
- ✅ **Featured Images** - Add featured images to posts
- ✅ **Reading Time** - Automatic reading time calculation
- ✅ **View Tracking** - Track post views
- ✅ **Search & Filter** - Advanced search and filtering
- ✅ **Bulk Actions** - Publish, draft, archive, or delete multiple posts
- ✅ **Auto-Registration** - Menu items automatically appear in admin sidebar

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or 12.0
- **noeticit/admin-core** (required)
- Vue 3 + Inertia.js
- Tailwind CSS

## Installation

### 1. Install via Composer

```bash
composer require noeticit/admin-blog
```

### 2. Run Migrations

```bash
php artisan migrate
```

The package will automatically create:
- `blog_categories` table
- `blog_tags` table
- `blog_posts` table
- `blog_post_tag` pivot table

### 3. Publish Assets (Optional)

```bash
# Publish configuration
php artisan vendor:publish --tag=blog-config

# Publish Vue components (to customize)
php artisan vendor:publish --tag=blog-assets
```

### 4. Build Frontend Assets

```bash
npm run build
```

### 5. Access the Blog Module

1. Login to your admin panel: `http://yourapp.test/admin/login`
2. You'll see "Blog" in the sidebar menu (auto-registered!)
3. Start creating posts

## Configuration

After publishing, edit `config/blog.php`:

```php
return [
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
```

### Environment Variables

Add to your `.env`:

```env
BLOG_TABLE_PREFIX=blog_
BLOG_AI_ENABLED=true
```

## Usage

### Auto-Registered Menu

The package automatically registers menu items in your admin sidebar:

- **Blog** (parent)
  - All Posts
  - New Post
  - Categories
  - Tags

No configuration needed! The menu appears automatically when the package is installed.

### Permissions

The following permissions are automatically registered:

- `blog.view` - View blog posts
- `blog.create` - Create new posts
- `blog.edit` - Edit posts
- `blog.delete` - Delete posts
- `blog.manage_categories` - Manage categories
- `blog.manage_tags` - Manage tags

Assign these permissions to admin roles via the admin core's permission system.

### Creating a Post

```php
use Noeticit\AdminBlog\Models\Post;

$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is the content...',
    'status' => 'published',
    'published_at' => now(),
    'author_id' => auth('admin')->id(),
    'category_id' => 1,
]);

// Add tags
$post->tags()->attach([1, 2, 3]);
```

### Querying Posts

```php
// Get published posts
$posts = Post::published()->get();

// Search posts
$posts = Post::search('laravel')->get();

// Filter by category
$posts = Post::byCategory('technology')->get();

// Filter by tag
$posts = Post::byTag('php')->get();

// Filter by status
$posts = Post::byStatus('published')->get();
```

### SEO Features

```php
$post = Post::find(1);

// Check if SEO is optimized
if ($post->isSEOOptimized()) {
    // All SEO fields are filled
}

// Get robots meta content
echo $post->getRobotsMetaContent(); // "index, follow"
```

### AI Integration

Generate SEO meta tags using AI:

```javascript
// In your Vue component
const generateMeta = async () => {
    const response = await fetch(route('admin.blog.posts.generate-meta', post.id), {
        method: 'POST',
    });

    const meta = await response.json();
    // meta.title, meta.description, meta.keywords
};
```

## Database Schema

### Posts Table

```sql
blog_posts
├── id
├── title
├── slug (unique)
├── content (longtext)
├── body_blocks (json)
├── featured_image
├── status (draft, published, archived)
├── published_at
├── meta_title
├── meta_description
├── meta_keywords (json)
├── og_title
├── og_description
├── og_image
├── twitter_title
├── twitter_description
├── twitter_image
├── canonical_url
├── focus_keyword
├── secondary_keywords (json)
├── schema_markup (json)
├── category_id (foreign)
├── author_id (foreign -> admin_users)
├── reading_time
├── word_count
├── views_count
├── robots_index (boolean)
├── robots_follow (boolean)
├── show_toc (boolean)
├── toc_data (json)
├── timestamps
└── soft_deletes
```

## API Reference

### Post Model

**Relationships:**
- `category()` - BelongsTo Category
- `author()` - BelongsTo AdminUser
- `tags()` - BelongsToMany Tag

**Scopes:**
- `published()` - Get published posts
- `search($query)` - Search posts
- `byCategory($slug)` - Filter by category
- `byTag($slug)` - Filter by tag
- `byStatus($status)` - Filter by status
- `byAuthor($id)` - Filter by author

**Methods:**
- `incrementViews()` - Increment view count
- `isPublished()` - Check if post is published
- `getRobotsMetaContent()` - Get robots meta string
- `isSEOOptimized()` - Check if SEO fields are complete

## Frontend Components

After publishing assets, Vue components are available in `resources/js/vendor/admin-blog/`:

```
resources/js/vendor/admin-blog/
└── Pages/
    ├── Posts/
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   └── Show.vue
    ├── Categories/
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Edit.vue
    └── Tags/
        ├── Index.vue
        ├── Create.vue
        └── Edit.vue
```

## Updating the Package

```bash
# Pull latest changes
composer update noeticit/admin-blog

# Republish assets (if needed)
php artisan vendor:publish --tag=blog-assets --force

# Run new migrations (if any)
php artisan migrate

# Rebuild
npm run build
```

## Customization

### Override Vue Components

Copy the component you want to customize:

```bash
cp resources/js/vendor/admin-blog/Pages/Posts/Index.vue \
   resources/js/pages/Admin/Blog/Posts/Index.vue
```

Update your route to use the custom component.

### Extend Post Model

```php
namespace App\Models;

use Noeticit\AdminBlog\Models\Post as BasePost;

class Post extends BasePost
{
    // Add your custom methods
    public function customMethod()
    {
        // ...
    }
}
```

Then update the config to use your custom model.

## Testing

```bash
# Run package tests
composer test

# Run with coverage
composer test-coverage
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

This is proprietary software for Noetic IT Services. All rights reserved.

## Support

For issues and feature requests, please use the GitHub issue tracker.

---

**Package**: noeticit/admin-blog
**Version**: 1.0.0
**Author**: Noetic IT Services Development Team
