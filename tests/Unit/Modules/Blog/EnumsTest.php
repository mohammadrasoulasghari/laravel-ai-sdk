<?php

declare(strict_types=1);

use App\Modules\Blog\Enums\BlogPermission;
use App\Modules\Blog\Enums\PostSort;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Roles\Enums\Role;
use App\Shared\Auth\PermissionRegistry;

describe('PostSort', function (): void {
    it('maps each value to a column and direction', function (PostSort $sort, string $value, string $column, string $direction): void {
        expect($sort->value)->toBe($value)
            ->and($sort->column())->toBe($column)
            ->and($sort->direction())->toBe($direction);
    })->with([
        'newest first' => [PostSort::NewestFirst, '-published_at', 'published_at', 'desc'],
        'oldest first' => [PostSort::OldestFirst, 'published_at', 'published_at', 'asc'],
        'title ascending' => [PostSort::TitleAsc, 'title', 'title', 'asc'],
        'title descending' => [PostSort::TitleDesc, '-title', 'title', 'desc'],
    ]);

    it('only allows the documented sort values', function (): void {
        expect(array_column(PostSort::cases(), 'value'))->toBe(['-published_at', 'published_at', 'title', '-title'])
            ->and(PostSort::tryFrom('created_at'))->toBeNull();
    });
});

it('has draft and published statuses', function (): void {
    expect(array_column(PostStatus::cases(), 'value'))->toBe(['draft', 'published']);
});

it('registers the blog permissions for admins', function (): void {
    expect(BlogPermission::ManagePosts->value)->toBe('blog.posts.manage')
        ->and(BlogPermission::ManageCategories->value)->toBe('blog.categories.manage')
        ->and(resolve(PermissionRegistry::class)->all())
        ->toHaveKey('blog.posts.manage', [Role::Admin->value])
        ->toHaveKey('blog.categories.manage', [Role::Admin->value]);
});
