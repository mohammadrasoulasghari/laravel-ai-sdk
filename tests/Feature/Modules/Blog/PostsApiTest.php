<?php

declare(strict_types=1);

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

use function Tests\userWithRole;

beforeEach(function (): void {
    Notification::fake();
});

describe('GET /api/v1/blog/posts', function (): void {
    it('returns a paginated collection of published posts', function (): void {
        $post = Post::factory()->published()->create(['body' => 'Hello **API**']);

        $this->getJson(route('api.v1.blog.posts.index'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id', 'title', 'slug', 'excerpt', 'body', 'body_html', 'status', 'published_at',
                    'category' => ['id', 'name', 'slug'],
                    'author' => ['id', 'name'],
                    'created_at', 'updated_at',
                ]],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
            ])
            ->assertJsonPath('data.0.id', $post->id)
            ->assertJsonPath('data.0.slug', $post->slug->value)
            ->assertJsonPath('data.0.status', 'published')
            ->assertJsonPath('data.0.body_html', "<p>Hello <strong>API</strong></p>\n")
            ->assertJsonPath('data.0.category.id', $post->category_id)
            ->assertJsonPath('data.0.author.id', $post->author_id)
            ->assertJsonMissingPath('data.0.author.email')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 1);
    });

    it('excludes drafts and scheduled posts', function (): void {
        $live = Post::factory()->published()->create();
        Post::factory()->create();
        Post::factory()->published()->create(['published_at' => now()->addDay()]);

        $this->getJson(route('api.v1.blog.posts.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $live->id);
    });

    it('searches titles and excerpts with filter[search]', function (): void {
        Post::factory()->published()->create(['title' => 'Laravel queues', 'excerpt' => 'Jobs']);
        Post::factory()->published()->create(['title' => 'Background work', 'excerpt' => 'All about Laravel']);
        Post::factory()->published()->create(['title' => 'Cooking', 'excerpt' => 'Pasta']);

        $titles = $this->getJson(route('api.v1.blog.posts.index', ['filter' => ['search' => 'Laravel']]))
            ->assertOk()
            ->json('data.*.title');

        expect($titles)->toEqualCanonicalizing(['Laravel queues', 'Background work']);
    });

    it('filters by category slug with filter[category]', function (): void {
        $news = Category::factory()->create(['slug' => 'news']);
        $inNews = Post::factory()->published()->inCategory($news)->create();
        Post::factory()->published()->create();

        $this->getJson(route('api.v1.blog.posts.index', ['filter' => ['category' => 'news']]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $inNews->id)
            ->assertJsonPath('data.0.category.slug', 'news');
    });

    it('sorts', function (string $sort, array $expected): void {
        Post::factory()->published()->create(['title' => 'Banana', 'published_at' => now()->subDays(1)]);
        Post::factory()->published()->create(['title' => 'Apple', 'published_at' => now()->subDays(2)]);
        Post::factory()->published()->create(['title' => 'Cherry', 'published_at' => now()->subDays(3)]);

        $titles = $this->getJson(route('api.v1.blog.posts.index', array_filter(['sort' => $sort])))
            ->assertOk()
            ->json('data.*.title');

        expect($titles)->toBe($expected);
    })->with([
        'default is newest first' => ['', ['Banana', 'Apple', 'Cherry']],
        'oldest first' => ['published_at', ['Cherry', 'Apple', 'Banana']],
        'title ascending' => ['title', ['Apple', 'Banana', 'Cherry']],
        'title descending' => ['-title', ['Cherry', 'Banana', 'Apple']],
    ]);

    it('paginates with per_page up to 100', function (): void {
        Post::factory()->count(3)->published()->create();

        $this->getJson(route('api.v1.blog.posts.index', ['per_page' => 2]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('links.next', route('api.v1.blog.posts.index', ['per_page' => 2, 'page' => 2]));

        $this->getJson(route('api.v1.blog.posts.index', ['per_page' => 100]))
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100);
    });

    it('rejects invalid query parameters with problem+json', function (array $query, string $field): void {
        $this->getJson(route('api.v1.blog.posts.index', $query))
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('status', 422)
            ->assertJsonPath('detail', __('problems.validation_failed'))
            ->assertJsonValidationErrors($field);
    })->with([
        'per_page above 100' => [['per_page' => 101], 'per_page'],
        'per_page below 1' => [['per_page' => 0], 'per_page'],
        'unknown filter key' => [['filter' => ['status' => 'draft']], 'filter'],
        'unknown sort' => [['sort' => 'created_at'], 'sort'],
        'page below 1' => [['page' => 0], 'page'],
    ]);
});

describe('GET /api/v1/blog/posts/{slug}', function (): void {
    it('shows a published post with category and author', function (): void {
        $post = Post::factory()->published()->create();

        $this->getJson(route('api.v1.blog.posts.show', $post->slug->value))
            ->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title)
            ->assertJsonPath('data.published_at', $post->published_at?->toIso8601String())
            ->assertJsonPath('data.category.slug', $post->category->slug->value)
            ->assertJsonPath('data.author.name', $post->author->name);
    });

    it('returns a 404 problem for posts that are not public', function (string $state): void {
        $post = match ($state) {
            'draft' => Post::factory()->create(),
            'scheduled' => Post::factory()->published()->create(['published_at' => now()->addDay()]),
        };

        $this->getJson(route('api.v1.blog.posts.show', $post->slug->value))
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('status', 404)
            ->assertJsonPath('instance', '/api/v1/blog/posts/'.$post->slug->value);
    })->with(['draft', 'scheduled']);
});

describe('POST /api/v1/blog/posts', function (): void {
    it('creates a draft for the authenticated user', function (): void {
        $admin = userWithRole(Role::Admin->value);
        $category = Category::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson(route('api.v1.blog.posts.store'), [
            'title' => 'From the API',
            'body' => 'Body',
            'category_id' => $category->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'from-the-api')
            ->assertJsonPath('data.status', PostStatus::Draft->value)
            ->assertJsonPath('data.published_at', null)
            ->assertJsonPath('data.author.id', $admin->id)
            ->assertJsonPath('data.category.id', $category->id);

        expect(Post::query()->sole()->author_id)->toBe($admin->id);
    });

    it('requires authentication', function (): void {
        $this->postJson(route('api.v1.blog.posts.store'), ['title' => 'x'])
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('status', 401);
    });

    it('forbids members', function (): void {
        Sanctum::actingAs(userWithRole(Role::Member->value));

        $this->postJson(route('api.v1.blog.posts.store'), [
            'title' => 'Nope',
            'body' => 'Body',
            'category_id' => Category::factory()->create()->id,
        ])
            ->assertForbidden()
            ->assertJsonPath('status', 403);

        expect(Post::query()->count())->toBe(0);
    });

    it('validates the payload', function (): void {
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->postJson(route('api.v1.blog.posts.store'), ['slug' => 'Bad Slug', 'category_id' => 'unknown'])
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonValidationErrors(['title', 'body', 'slug', 'category_id']);
    });
});

describe('PUT /api/v1/blog/posts/{id}', function (): void {
    it('updates a post addressed by id', function (string $method): void {
        $post = Post::factory()->published()->create(['slug' => 'old-slug']);
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->json($method, route('api.v1.blog.posts.update', $post->id), [
            'title' => 'Updated',
            'slug' => 'new-slug',
            'body' => 'New body',
            'category_id' => $post->category_id,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated')
            ->assertJsonPath('data.slug', 'new-slug')
            ->assertJsonPath('data.status', 'published');

        expect($post->refresh()->slug->value)->toBe('new-slug');
    })->with(['PUT', 'PATCH']);

    it('returns 401, 403 and 404 problems', function (): void {
        $post = Post::factory()->create(['title' => 'Original']);
        $payload = ['title' => 'Hacked', 'body' => 'x', 'category_id' => $post->category_id];

        $this->putJson(route('api.v1.blog.posts.update', $post->id), $payload)->assertUnauthorized();

        Sanctum::actingAs(userWithRole(Role::Member->value));
        $this->putJson(route('api.v1.blog.posts.update', $post->id), $payload)->assertForbidden();

        Sanctum::actingAs(userWithRole(Role::Admin->value));
        $this->putJson(route('api.v1.blog.posts.update', '01JZZZZZZZZZZZZZZZZZZZZZZZ'), $payload)
            ->assertNotFound()
            ->assertJsonPath('status', 404);

        expect($post->refresh()->title)->toBe('Original');
    });

    it('rejects a slug owned by another post', function (): void {
        Post::factory()->create(['slug' => 'taken']);
        $post = Post::factory()->create();
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->putJson(route('api.v1.blog.posts.update', $post->id), [
            'title' => 'Title',
            'slug' => 'taken',
            'body' => 'Body',
            'category_id' => $post->category_id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    });
});

describe('DELETE /api/v1/blog/posts/{id}', function (): void {
    it('deletes a post', function (): void {
        $post = Post::factory()->create();
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->deleteJson(route('api.v1.blog.posts.destroy', $post->id))->assertNoContent();

        $this->assertModelMissing($post);
    });

    it('requires authentication and permission', function (): void {
        $post = Post::factory()->create();

        $this->deleteJson(route('api.v1.blog.posts.destroy', $post->id))->assertUnauthorized();

        Sanctum::actingAs(userWithRole(Role::Member->value));
        $this->deleteJson(route('api.v1.blog.posts.destroy', $post->id))->assertForbidden();

        $this->assertModelExists($post);
    });
});

describe('publication', function (): void {
    it('publishes a draft', function (): void {
        $post = Post::factory()->create();
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->postJson(route('api.v1.blog.posts.publish', $post->id))
            ->assertOk()
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.published_at', fn (?string $value): bool => $value !== null);

        expect($post->refresh()->status)->toBe(PostStatus::Published);
    });

    it('returns a 409 problem when publishing twice', function (): void {
        $post = Post::factory()->create();
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->postJson(route('api.v1.blog.posts.publish', $post->id))->assertOk();

        $this->postJson(route('api.v1.blog.posts.publish', $post->id))
            ->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('type', 'urn:problem:blog.post_already_published')
            ->assertJsonPath('status', 409)
            ->assertJsonPath('detail', __('blog::posts.errors.already_published'))
            ->assertJsonPath('instance', "/api/v1/blog/posts/{$post->id}/publication");
    });

    it('translates the problem detail to the requested locale', function (): void {
        $post = Post::factory()->published()->create();
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->postJson(route('api.v1.blog.posts.publish', $post->id), [], ['Accept-Language' => 'fa'])
            ->assertStatus(409)
            ->assertHeader('Content-Language', 'fa')
            ->assertJsonPath('detail', __('blog::posts.errors.already_published', locale: 'fa'));
    });

    it('unpublishes a published post', function (): void {
        $post = Post::factory()->published()->create();
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->deleteJson(route('api.v1.blog.posts.unpublish', $post->id))
            ->assertOk()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null);
    });

    it('returns a 409 problem when unpublishing a draft', function (): void {
        $post = Post::factory()->create();
        Sanctum::actingAs(userWithRole(Role::Admin->value));

        $this->deleteJson(route('api.v1.blog.posts.unpublish', $post->id))
            ->assertStatus(409)
            ->assertJsonPath('type', 'urn:problem:blog.post_not_published');
    });

    it('requires authentication and permission', function (): void {
        $post = Post::factory()->create();

        $this->postJson(route('api.v1.blog.posts.publish', $post->id))->assertUnauthorized();

        Sanctum::actingAs(userWithRole(Role::Member->value));
        $this->postJson(route('api.v1.blog.posts.publish', $post->id))
            ->assertForbidden()
            ->assertHeader('Content-Type', 'application/problem+json');

        expect($post->refresh()->status)->toBe(PostStatus::Draft);
    });
});

it('lets a token holder use the API end to end', function (): void {
    $admin = userWithRole(Role::Admin->value);
    $token = $admin->createToken('cli')->plainTextToken;
    $category = Category::factory()->create();

    $id = $this->withToken($token)
        ->postJson(route('api.v1.blog.posts.store'), ['title' => 'Token post', 'body' => 'Body', 'category_id' => $category->id])
        ->assertCreated()
        ->json('data.id');

    $this->withToken($token)->postJson(route('api.v1.blog.posts.publish', $id))->assertOk();

    $this->getJson(route('api.v1.blog.posts.show', 'token-post'))
        ->assertOk()
        ->assertJsonPath('data.author.id', $admin->id);

    expect(User::query()->count())->toBe(1);
});
