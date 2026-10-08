<?php

declare(strict_types=1);

use App\Modules\Blog\Actions\GenerateUniqueSlug;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;

it('returns the slug unchanged when it is free', function (): void {
    $slug = resolve(GenerateUniqueSlug::class)->handle(new Slug('hello-world'), Post::class);

    expect($slug->value)->toBe('hello-world');
});

it('appends -2, -3, … until the slug is unused', function (): void {
    Post::factory()->create(['slug' => 'hello-world']);
    Post::factory()->create(['slug' => 'hello-world-2']);

    $slug = resolve(GenerateUniqueSlug::class)->handle(new Slug('hello-world'), Post::class);

    expect($slug->value)->toBe('hello-world-3');
});

it('ignores the record that already owns the slug', function (): void {
    $post = Post::factory()->create(['slug' => 'hello-world']);

    $slug = resolve(GenerateUniqueSlug::class)->handle(new Slug('hello-world'), Post::class, $post->id);

    expect($slug->value)->toBe('hello-world');
});

it('checks uniqueness per model table', function (): void {
    Category::factory()->create(['slug' => 'news']);

    expect(resolve(GenerateUniqueSlug::class)->handle(new Slug('news'), Post::class)->value)->toBe('news')
        ->and(resolve(GenerateUniqueSlug::class)->handle(new Slug('news'), Category::class)->value)->toBe('news-2');
});

it('suffixes Persian slugs too', function (): void {
    Post::factory()->create(['slug' => 'سلام-دنیا']);

    expect(resolve(GenerateUniqueSlug::class)->handle(new Slug('سلام-دنیا'), Post::class)->value)->toBe('سلام-دنیا-2');
});
