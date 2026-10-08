<?php

declare(strict_types=1);

use App\Modules\Blog\Actions\CreatePost;
use App\Modules\Blog\Actions\DeletePost;
use App\Modules\Blog\Actions\UpdatePost;
use App\Modules\Blog\Data\PostData;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;
use App\Modules\Users\Models\User;

describe('CreatePost', function (): void {
    it('creates a draft owned by the author', function (): void {
        $author = User::factory()->create();
        $category = Category::factory()->create();

        $post = resolve(CreatePost::class)->handle(
            new PostData(title: 'Hello World', body: '# Hi', categoryId: $category->id, excerpt: 'Short'),
            $author,
        );

        expect($post->exists)->toBeTrue()
            ->and($post->status)->toBe(PostStatus::Draft)
            ->and($post->published_at)->toBeNull()
            ->and($post->author_id)->toBe($author->id)
            ->and($post->category_id)->toBe($category->id)
            ->and($post->slug->value)->toBe('hello-world')
            ->and($post->excerpt)->toBe('Short')
            ->and($post->body)->toBe('# Hi');
    });

    it('generates the slug from a Persian title', function (): void {
        $post = resolve(CreatePost::class)->handle(
            new PostData(title: 'سلام دنیا', body: 'متن', categoryId: Category::factory()->create()->id),
            User::factory()->create(),
        );

        expect($post->slug->value)->toBe('سلام-دنیا');
    });

    it('uses an explicit slug when given', function (): void {
        $post = resolve(CreatePost::class)->handle(
            new PostData(title: 'Hello World', body: 'Body', categoryId: Category::factory()->create()->id, slug: new Slug('custom')),
            User::factory()->create(),
        );

        expect($post->slug->value)->toBe('custom');
    });

    it('suffixes the slug when the title is reused', function (): void {
        $author = User::factory()->create();
        $data = new PostData(title: 'Same Title', body: 'Body', categoryId: Category::factory()->create()->id);
        $create = resolve(CreatePost::class);

        $slugs = [
            $create->handle($data, $author)->slug->value,
            $create->handle($data, $author)->slug->value,
            $create->handle($data, $author)->slug->value,
        ];

        expect($slugs)->toBe(['same-title', 'same-title-2', 'same-title-3']);
    });
});

describe('UpdatePost', function (): void {
    it('updates the editable fields and keeps the slug when none is given', function (): void {
        $post = Post::factory()->create(['slug' => 'original']);
        $category = Category::factory()->create();

        resolve(UpdatePost::class)->handle($post, new PostData(title: 'New Title', body: 'New body', categoryId: $category->id));

        $post->refresh();

        expect($post->title)->toBe('New Title')
            ->and($post->body)->toBe('New body')
            ->and($post->excerpt)->toBeNull()
            ->and($post->category_id)->toBe($category->id)
            ->and($post->slug->value)->toBe('original');
    });

    it('changes the slug when a new one is given', function (): void {
        $post = Post::factory()->create(['slug' => 'original']);

        resolve(UpdatePost::class)->handle($post, new PostData(title: $post->title, body: $post->body, categoryId: $post->category_id, slug: new Slug('renamed')));

        expect($post->refresh()->slug->value)->toBe('renamed');
    });

    it('suffixes a new slug that is taken by another post', function (): void {
        Post::factory()->create(['slug' => 'taken']);
        $post = Post::factory()->create(['slug' => 'original']);

        resolve(UpdatePost::class)->handle($post, new PostData(title: $post->title, body: $post->body, categoryId: $post->category_id, slug: new Slug('taken')));

        expect($post->refresh()->slug->value)->toBe('taken-2');
    });

    it('does not suffix the post’s own slug', function (): void {
        $post = Post::factory()->create(['slug' => 'mine']);

        resolve(UpdatePost::class)->handle($post, new PostData(title: 'Changed', body: $post->body, categoryId: $post->category_id, slug: new Slug('mine')));

        expect($post->refresh()->slug->value)->toBe('mine');
    });

    it('keeps the publication state', function (): void {
        $post = Post::factory()->published()->create();

        resolve(UpdatePost::class)->handle($post, new PostData(title: 'Edited live post', body: $post->body, categoryId: $post->category_id));

        expect($post->refresh()->status)->toBe(PostStatus::Published)
            ->and($post->published_at)->not->toBeNull();
    });
});

it('deletes a post', function (): void {
    $post = Post::factory()->published()->create();

    resolve(DeletePost::class)->handle($post);

    $this->assertModelMissing($post);
});
