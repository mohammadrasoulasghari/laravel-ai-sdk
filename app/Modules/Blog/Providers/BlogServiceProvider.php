<?php

declare(strict_types=1);

namespace App\Modules\Blog\Providers;

use App\Modules\Blog\Enums\BlogPermission;
use App\Modules\Blog\Events\PostPublished;
use App\Modules\Blog\Listeners\NotifyAuthorOfPublication;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Policies\CategoryPolicy;
use App\Modules\Blog\Policies\PostPolicy;
use App\Modules\Roles\Enums\Role;
use App\Shared\Auth\PermissionRegistry;
use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;
use App\Shared\Providers\ModuleServiceProvider;

/**
 * Reference module. Copy its structure for new modules (or run `php artisan make:module`).
 */
final class BlogServiceProvider extends ModuleServiceProvider
{
    /** @var array<class-string, class-string> */
    protected array $policies = [
        Post::class => PostPolicy::class,
        Category::class => CategoryPolicy::class,
    ];

    /** @var array<class-string, list<class-string>> */
    protected array $listen = [
        PostPublished::class => [NotifyAuthorOfPublication::class],
    ];

    protected function bootModule(): void
    {
        $this->app->make(PermissionRegistry::class)->register(BlogPermission::cases(), [Role::Admin->value]);

        $navigation = $this->app->make(Navigation::class);
        $navigation->add(new NavigationItem(NavigationArea::App, 'blog::posts.nav', 'blog.posts.index', active: 'blog.*', order: 20));
        $navigation->add(new NavigationItem(NavigationArea::Admin, 'blog::posts.admin_nav', 'admin.blog.posts.index', BlogPermission::ManagePosts->value, 'admin.blog.posts.*', order: 40));
        $navigation->add(new NavigationItem(NavigationArea::Admin, 'blog::categories.admin_nav', 'admin.blog.categories.index', BlogPermission::ManageCategories->value, 'admin.blog.categories.*', order: 41));
    }
}
