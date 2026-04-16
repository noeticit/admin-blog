<?php

namespace Noeticit\AdminBlog;

use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/blog.php', 'blog'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/blog.php');

        if (config('blog.api.enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        $this->registerPublishables();

        // Register menu and permissions only if admin-core is installed
        if (class_exists(\Noeticit\Admin\Services\MenuRegistry::class)) {
            $this->registerMenu();
            $this->registerPermissions();
        }
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/blog.php' => config_path('blog.php'),
        ], 'blog-config');

        $this->publishes([
            __DIR__.'/../resources/js/Pages' => resource_path('js/Pages/Admin/Blog'),
            __DIR__.'/../resources/js/Components' => resource_path('js/components/admin-blog'),
        ], 'blog-assets');

        $this->publishes([
            __DIR__.'/../resources/js/Pages' => resource_path('js/Pages/Admin/Blog'),
        ], 'blog-pages');

        $this->publishes([
            __DIR__.'/../resources/js/Components' => resource_path('js/components/admin-blog'),
        ], 'blog-components');

        $this->publishes([
            __DIR__.'/../config/blog.php' => config_path('blog.php'),
            __DIR__.'/../resources/js/Pages' => resource_path('js/Pages/Admin/Blog'),
            __DIR__.'/../resources/js/Components' => resource_path('js/components/admin-blog'),
        ], 'blog-all');
    }

    protected function registerMenu(): void
    {
        \Noeticit\Admin\Services\MenuRegistry::register([
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

    protected function registerPermissions(): void
    {
        \Noeticit\Admin\Services\PermissionRegistry::register('admin-blog', [
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
