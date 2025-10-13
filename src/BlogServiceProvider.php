<?php

namespace Noeticit\AdminBlog;

use Illuminate\Support\ServiceProvider;
use Noeticit\Admin\Services\MenuRegistry;
use Noeticit\Admin\Services\PermissionRegistry;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/blog.php', 'blog'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/blog.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // Register publishables
        $this->registerPublishables();

        // Register menu items
        $this->registerMenu();

        // Register permissions
        $this->registerPermissions();
    }

    /**
     * Register publishable assets.
     */
    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/blog.php' => config_path('blog.php'),
            ], 'blog-config');

            // Publish Vue pages directly into pages directory
            $this->publishes([
                __DIR__.'/../resources/js/Pages' => resource_path('js/pages/Admin/Blog'),
                __DIR__.'/../resources/js/Components' => resource_path('js/components/admin-blog'),
            ], 'blog-assets');

            // Publish pages only (for selective publishing)
            $this->publishes([
                __DIR__.'/../resources/js/Pages' => resource_path('js/pages/Admin/Blog'),
            ], 'blog-pages');

            // Publish all
            $this->publishes([
                __DIR__.'/../config/blog.php' => config_path('blog.php'),
                __DIR__.'/../resources/js/Pages' => resource_path('js/pages/Admin/Blog'),
                __DIR__.'/../resources/js/Components' => resource_path('js/components/admin-blog'),
            ], 'blog-all');
        }
    }

    /**
     * Register menu items.
     */
    protected function registerMenu(): void
    {
        MenuRegistry::register([
            'label' => 'Blog',
            'icon' => 'FileText',
            'route' => 'admin.blog.posts.index',
            'permission' => 'blog.view',
            'order' => 10,
            'module' => 'admin-blog',
            'children' => [
                [
                    'label' => 'All Posts',
                    'route' => 'admin.blog.posts.index',
                    'permission' => 'blog.view',
                ],
                [
                    'label' => 'New Post',
                    'route' => 'admin.blog.posts.create',
                    'permission' => 'blog.create',
                ],
                [
                    'label' => 'Categories',
                    'route' => 'admin.blog.categories.index',
                    'permission' => 'blog.manage_categories',
                ],
                [
                    'label' => 'Tags',
                    'route' => 'admin.blog.tags.index',
                    'permission' => 'blog.manage_tags',
                ],
            ],
        ]);
    }

    /**
     * Register permissions.
     */
    protected function registerPermissions(): void
    {
        PermissionRegistry::register('admin-blog', [
            [
                'name' => 'View Blog Posts',
                'slug' => 'blog.view',
                'description' => 'Can view blog posts in admin panel',
            ],
            [
                'name' => 'Create Blog Posts',
                'slug' => 'blog.create',
                'description' => 'Can create new blog posts',
            ],
            [
                'name' => 'Edit Blog Posts',
                'slug' => 'blog.edit',
                'description' => 'Can edit existing blog posts',
            ],
            [
                'name' => 'Delete Blog Posts',
                'slug' => 'blog.delete',
                'description' => 'Can delete blog posts',
            ],
            [
                'name' => 'Manage Categories',
                'slug' => 'blog.manage_categories',
                'description' => 'Can create, edit, and delete categories',
            ],
            [
                'name' => 'Manage Tags',
                'slug' => 'blog.manage_tags',
                'description' => 'Can create, edit, and delete tags',
            ],
        ]);
    }
}
