<?php

declare(strict_types=1);

use App\Modules\Blog\Enums\BlogPermission;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Roles\Enums\Role;
use App\Shared\Auth\SystemAbility;
use App\Shared\Http\Flash;
use Illuminate\Support\Facades\Notification;

use function Tests\userWithPermissions;
use function Tests\userWithRole;

// [HTTP method, route name, whether the route needs a bound post]
dataset('admin post routes', [
    'index' => ['get', 'admin.blog.posts.index', false],
    'create' => ['get', 'admin.blog.posts.create', false],
    'store' => ['post', 'admin.blog.posts.store', false],
    'edit' => ['get', 'admin.blog.posts.edit', true],
    'update' => ['put', 'admin.blog.posts.update', true],
    'destroy' => ['delete', 'admin.blog.posts.destroy', true],
    'publish' => ['post', 'admin.blog.posts.publish', true],
    'unpublish' => ['delete', 'admin.blog.posts.unpublish', true],
]);

beforeEach(function (): void {
    Notification::fake();
});

describe('access', function (): void {
    it('redirects guests to login', function (string $method, string $route, bool $needsPost): void {
        $this->{$method}(route($route, $needsPost ? Post::factory()->create() : []))
            ->assertRedirect(route('login'));
    })->with('admin post routes');

    it('forbids members', function (string $method, string $route, bool $needsPost): void {
        $this->actingAs(userWithRole(Role::Member->value))
            ->{$method}(route($route, $needsPost ? Post::factory()->create() : []))
            ->assertForbidden();
    })->with('admin post routes');

    it('forbids admin panel users without blog.posts.manage', function (string $method, string $route, bool $needsPost): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, BlogPermission::ManageCategories->value))
            ->{$method}(route($route, $needsPost ? Post::factory()->create() : []))
            ->assertForbidden();
    })->with('admin post routes');

    it('lets users with exactly the needed permissions in', function (): void {
        $this->actingAs(userWithPermissions(SystemAbility::AccessAdmin->value, BlogPermission::ManagePosts->value))
            ->get(route('admin.blog.posts.index'))
            ->assertOk()
            ->assertSee('href="'.route('admin.blog.posts.index').'"', false)
            ->assertDontSee('href="'.route('admin.blog.categories.index').'"', false);
    });

    it('does not change anything when forbidden', function (): void {
        $post = Post::factory()->create(['title' => 'Untouched']);

        $this->actingAs(userWithRole(Role::Member->value))
            ->put(route('admin.blog.posts.update', $post), ['title' => 'Hacked', 'body' => 'x', 'category_id' => $post->category_id])
            ->assertForbidden();

        expect($post->refresh()->title)->toBe('Untouched');
    });
});

describe('index', function (): void {
    it('lists drafts and published posts', function (): void {
        $draft = Post::factory()->create(['title' => 'A draft']);
        Post::factory()->published()->create(['title' => 'A live post']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.blog.posts.index'))
            ->assertOk()
            ->assertSee(__('blog::posts.admin_title'))
            ->assertSee('A draft')
            ->assertSee('A live post')
            ->assertSee(__('blog::posts.status.draft'))
            ->assertSee(__('blog::posts.status.published'))
            ->assertSee(route('admin.blog.posts.edit', $draft), false);
    });

    it('searches by title', function (): void {
        Post::factory()->create(['title' => 'Laravel tips']);
        Post::factory()->create(['title' => 'Cooking pasta']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.blog.posts.index', ['search' => 'Laravel']))
            ->assertOk()
            ->assertSee('Laravel tips')
            ->assertDontSee('Cooking pasta')
            ->assertSee('value="Laravel"', false);
    });
});

describe('create and store', function (): void {
    it('shows the create form with category options', function (): void {
        $category = Category::factory()->create(['name' => 'Announcements']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.blog.posts.create'))
            ->assertOk()
            ->assertSee(__('blog::posts.create_title'))
            ->assertSee('value="'.$category->id.'"', false)
            ->assertSee('Announcements');
    });

    it('creates a draft and redirects to its edit page', function (): void {
        $admin = userWithRole(Role::Admin->value);
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.blog.posts.store'), [
                'title' => 'Hello World',
                'excerpt' => 'Short',
                'body' => '# Hi',
                'category_id' => $category->id,
            ])
            ->assertRedirect(route('admin.blog.posts.edit', 'hello-world'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::posts.created')]);

        $post = Post::query()->sole();

        expect($post->slug->value)->toBe('hello-world')
            ->and($post->status)->toBe(PostStatus::Draft)
            ->and($post->author_id)->toBe($admin->id)
            ->and($post->category_id)->toBe($category->id);
    });

    it('validates the post', function (array $payload, string $field): void {
        Post::factory()->create(['slug' => 'taken']);
        $category = Category::factory()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.blog.posts.create'))
            ->post(route('admin.blog.posts.store'), [
                'title' => 'Valid title',
                'body' => 'Valid body',
                'category_id' => $category->id,
                ...$payload,
            ])
            ->assertRedirect(route('admin.blog.posts.create'))
            ->assertSessionHasErrors($field);

        expect(Post::query()->count())->toBe(1);
    })->with([
        'missing title' => [['title' => ''], 'title'],
        'title too long' => [['title' => str_repeat('a', 201)], 'title'],
        'missing body' => [['body' => ''], 'body'],
        'uppercase slug' => [['slug' => 'Bad Slug'], 'slug'],
        'slug with trailing hyphen' => [['slug' => 'bad-'], 'slug'],
        'duplicate slug' => [['slug' => 'taken'], 'slug'],
        'excerpt too long' => [['excerpt' => str_repeat('a', 501)], 'excerpt'],
        'missing category' => [['category_id' => ''], 'category_id'],
        'unknown category' => [['category_id' => '01JZZZZZZZZZZZZZZZZZZZZZZZ'], 'category_id'],
    ]);
});

describe('edit and update', function (): void {
    it('shows the edit form with publish controls for drafts', function (): void {
        $post = Post::factory()->create(['title' => 'Editable']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.blog.posts.edit', $post))
            ->assertOk()
            ->assertSee(__('blog::posts.edit_title'))
            ->assertSee('value="Editable"', false)
            ->assertSee('action="'.route('admin.blog.posts.publish', $post).'"', false)
            ->assertSee(__('blog::posts.publish'))
            ->assertDontSee(__('blog::posts.unpublish'))
            ->assertDontSee(__('blog::posts.view_public'));
    });

    it('shows view and unpublish controls for published posts', function (): void {
        $post = Post::factory()->published()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.blog.posts.edit', $post))
            ->assertOk()
            ->assertSee('href="'.route('blog.posts.show', $post->slug->value).'"', false)
            ->assertSee(__('blog::posts.view_public'))
            ->assertSee(__('blog::posts.unpublish'));
    });

    it('updates the post and its slug', function (): void {
        $post = Post::factory()->create(['slug' => 'old-slug']);
        $category = Category::factory()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.blog.posts.update', $post), [
                'title' => 'Updated title',
                'slug' => 'new-slug',
                'body' => 'Updated body',
                'category_id' => $category->id,
            ])
            ->assertRedirect(route('admin.blog.posts.edit', 'new-slug'))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::posts.updated')]);

        $post->refresh();

        expect($post->title)->toBe('Updated title')
            ->and($post->slug->value)->toBe('new-slug')
            ->and($post->body)->toBe('Updated body')
            ->and($post->category_id)->toBe($category->id);
    });

    it('allows keeping the same slug', function (): void {
        $post = Post::factory()->create(['slug' => 'same']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.blog.posts.update', $post), [
                'title' => 'Same slug',
                'slug' => 'same',
                'body' => 'Body',
                'category_id' => $post->category_id,
            ])
            ->assertSessionHasNoErrors();

        expect($post->refresh()->slug->value)->toBe('same');
    });

    it('rejects a slug owned by another post', function (): void {
        Post::factory()->create(['slug' => 'taken']);
        $post = Post::factory()->create(['slug' => 'mine']);

        $this->actingAs(userWithRole(Role::Admin->value))
            ->put(route('admin.blog.posts.update', $post), [
                'title' => 'Title',
                'slug' => 'taken',
                'body' => 'Body',
                'category_id' => $post->category_id,
            ])
            ->assertSessionHasErrors('slug');

        expect($post->refresh()->slug->value)->toBe('mine');
    });

    it('returns 404 for an unknown post', function (): void {
        $this->actingAs(userWithRole(Role::Admin->value))
            ->get(route('admin.blog.posts.edit', 'no-such-post'))
            ->assertNotFound();
    });
});

it('deletes a post', function (): void {
    $post = Post::factory()->create();

    $this->actingAs(userWithRole(Role::Admin->value))
        ->delete(route('admin.blog.posts.destroy', $post))
        ->assertRedirect(route('admin.blog.posts.index'))
        ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::posts.deleted')]);

    $this->assertModelMissing($post);
});

describe('publication', function (): void {
    it('publishes a draft', function (): void {
        $post = Post::factory()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.blog.posts.edit', $post))
            ->post(route('admin.blog.posts.publish', $post))
            ->assertRedirect(route('admin.blog.posts.edit', $post))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::posts.published')]);

        expect($post->refresh()->status)->toBe(PostStatus::Published)
            ->and($post->published_at)->not->toBeNull();
    });

    it('flashes an error when publishing twice', function (): void {
        $post = Post::factory()->published()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.blog.posts.edit', $post))
            ->post(route('admin.blog.posts.publish', $post))
            ->assertRedirect(route('admin.blog.posts.edit', $post))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'error', 'message' => __('blog::posts.errors.already_published')]);
    });

    it('unpublishes a published post', function (): void {
        $post = Post::factory()->published()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.blog.posts.edit', $post))
            ->delete(route('admin.blog.posts.unpublish', $post))
            ->assertRedirect(route('admin.blog.posts.edit', $post))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'success', 'message' => __('blog::posts.unpublished')]);

        expect($post->refresh()->status)->toBe(PostStatus::Draft)
            ->and($post->published_at)->toBeNull();
    });

    it('flashes an error when unpublishing a draft', function (): void {
        $post = Post::factory()->create();

        $this->actingAs(userWithRole(Role::Admin->value))
            ->from(route('admin.blog.posts.edit', $post))
            ->delete(route('admin.blog.posts.unpublish', $post))
            ->assertSessionHas(Flash::SESSION_KEY, ['type' => 'error', 'message' => __('blog::posts.errors.not_published')]);
    });
});

it('lets super admins manage posts without explicit permissions', function (): void {
    $post = Post::factory()->create();

    $this->actingAs(userWithRole(Role::SuperAdmin->value))
        ->get(route('admin.blog.posts.edit', $post))
        ->assertOk();
});
