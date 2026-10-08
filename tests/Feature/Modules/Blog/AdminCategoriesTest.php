<?php

declare(strict_types=1);

use App\Modules\Blog\Enums\BlogPermission;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Roles\Enums\Role;
use App\Shared\Auth\SystemAbility;
use App\Shared\Http\Flash;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

// [HTTP method, route name, whether the route needs a bound category]
dataset('admin category routes', [
    'index' => ['get', 'admin.blog.categories.index', false],
    'store' => ['post', 'admin.blog.categories.store', false],
    'edit' => ['get', 'admin.blog.categories.edit', true],
    'update' => ['put', 'admin.blog.categories.update', true],
    'destroy' => ['delete', 'admin.blog.categories.destroy', true],
]);

describe('access', function (): void {
    it('redirects guests to login', function (string $method, string $route, bool $needsCategory): void {
        $this->{$method}(route($route, $needsCategory ? Category::factory()->create() : []))
            ->assertRedirect(route('login'));
    })->with('admin category routes');

    it('forbids members', function (string $method, string $route, bool $needsCategory): void {
        $this->actingAs(userWithRole(Role::Member->value))
            ->{$method}(route($route, $needsCategory ? Category::factory()->create() : []))
            ->assertForbidden();
    })->with('admin category routes');

    it('forbids post managers without blog.categories.manage', function (string $method, string $route, bool $needsCategory): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, BlogPermission::ManagePosts->value))
            ->{$method}(route($route, $needsCategory ? Category::factory()->create() : []))
            ->assertForbidden();
    })->with('admin category routes');
});

it('lists categories with their post counts', function (): void {
    $category = Category::factory()->create(['name' => 'Guides', 'slug' => 'guides']);
    Post::factory()->count(3)->inCategory($category)->create();

    $this->actingAs(userWithRole(Role::Admin->value))
        ->get(route('admin.blog.categories.index'))
        ->assertOk()
        ->assertSee(__('blog::categories.title'))
        ->assertSeeInOrder(['Guides', 'guides', '3'])
        ->assertSee(route('admin.blog.categories.edit', $category), false);
});

describe('store', function (): void {
    it('creates a category with a generated slug', function (): void {
        $this->actingAs(userWithRole(Role::Admin->value))
            ->post(route('admin.blog.categories.store'), ['name' => 'اخبار فناوری'])
            ->assertRedirect(route('admin.blog.categories.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::categories.created')]);

        expect(Category::query()->sole()->slug->value)->toBe('اخبار-فناوری');
    });

    it('validates the category', function (array $payload, string $field): void {
        Category::factory()->create(['slug' => 'taken']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.blog.categories.index'))
            ->post(route('admin.blog.categories.store'), ['name' => 'Valid', ...$payload])
            ->assertRedirect(route('admin.blog.categories.index'))
            ->assertSessionHasErrors($field);

        expect(Category::query()->count())->toBe(1);
    })->with([
        'missing name' => [['name' => ''], 'name'],
        'name too long' => [['name' => str_repeat('a', 101)], 'name'],
        'invalid slug' => [['slug' => 'Not Valid'], 'slug'],
        'duplicate slug' => [['slug' => 'taken'], 'slug'],
    ]);
});

describe('edit and update', function (): void {
    it('shows the edit form', function (): void {
        $category = Category::factory()->create(['name' => 'Guides', 'slug' => 'guides']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.blog.categories.edit', $category))
            ->assertOk()
            ->assertSee(__('blog::categories.edit_title'))
            ->assertSee('value="Guides"', false)
            ->assertSee('value="guides"', false);
    });

    it('updates name and slug', function (): void {
        $category = Category::factory()->create(['slug' => 'old']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.blog.categories.update', $category), ['name' => 'Renamed', 'slug' => 'renamed'])
            ->assertRedirect(route('admin.blog.categories.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::categories.updated')]);

        $category->refresh();

        expect($category->name)->toBe('Renamed')
            ->and($category->slug->value)->toBe('renamed');
    });

    it('allows keeping the same slug', function (): void {
        $category = Category::factory()->create(['slug' => 'same']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.blog.categories.update', $category), ['name' => 'Renamed', 'slug' => 'same'])
            ->assertSessionHasNoErrors();

        expect($category->refresh()->slug->value)->toBe('same');
    });
});

describe('destroy', function (): void {
    it('deletes an empty category', function (): void {
        $category = Category::factory()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->delete(route('admin.blog.categories.destroy', $category))
            ->assertRedirect(route('admin.blog.categories.index'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::categories.deleted')]);

        $this->assertModelMissing($category);
    });

    it('refuses to delete a category with posts and flashes why', function (): void {
        $category = Category::factory()->create();
        Post::factory()->count(2)->inCategory($category)->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.blog.categories.index'))
            ->delete(route('admin.blog.categories.destroy', $category))
            ->assertRedirect(route('admin.blog.categories.index'))
            ->assertSessionHas(Flash::SESSION_KEY, [
                'type' => 'error',
                'message' => __('blog::categories.errors.has_posts', ['count' => 2]),
            ]);

        $this->assertModelExists($category);
    });
});
