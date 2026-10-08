<?php

declare(strict_types=1);

use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\DB;

describe('Slug cast', function (): void {
    it('round-trips a slug through the database', function (string $value): void {
        $post = Post::factory()->create(['slug' => new Slug($value)]);

        $fresh = Post::query()->findOrFail($post->id);

        expect($fresh->slug)->toBeInstanceOf(Slug::class)
            ->and($fresh->slug->equals(new Slug($value)))->toBeTrue()
            ->and(DB::table('blog_posts')->where('id', $post->id)->value('slug'))->toBe($value);
    })->with(['hello-world', 'سلام-دنیا']);

    it('accepts plain strings and validates them', function (): void {
        $category = Category::factory()->create(['slug' => 'from-string']);

        expect($category->refresh()->slug->value)->toBe('from-string')
            ->and(fn (): Category => Category::factory()->create(['slug' => 'Not Valid']))
            ->toThrow(InvalidArgumentException::class);
    });

    it('finds models by slug value', function (): void {
        $post = Post::factory()->create(['slug' => 'find-me']);

        expect(Post::query()->where('slug', 'find-me')->sole()->id)->toBe($post->id)
            ->and($post->getRouteKeyName())->toBe('slug');
    });
});

describe('published scope', function (): void {
    it('includes only posts published in the past', function (): void {
        $live = Post::factory()->published()->create();
        Post::factory()->create();
        Post::factory()->published()->create(['published_at' => now()->addHour()]);

        expect(Post::query()->published()->pluck('id')->all())->toBe([$live->id]);
    });
});

describe('body_html', function (): void {
    it('renders markdown', function (): void {
        $post = Post::factory()->make(['body' => "## Heading\n\nSome **bold** text and a [link](https://example.com)."]);

        expect($post->body_html)
            ->toContain('<h2>Heading</h2>')
            ->toContain('<strong>bold</strong>')
            ->toContain('<a href="https://example.com">link</a>');
    });

    it('strips raw HTML and unsafe links', function (): void {
        $post = Post::factory()->make(['body' => "Hi\n\n<script>alert(1)</script>\n\n<b onclick=\"x()\">b</b> [x](javascript:alert(1))"]);

        expect($post->body_html)
            ->not->toContain('<script')
            ->not->toContain('onclick')
            ->not->toContain('javascript:');
    });
});

it('belongs to a category and an author', function (): void {
    $author = User::factory()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->inCategory($category)->for($author, 'author')->create();

    expect($post->category->is($category))->toBeTrue()
        ->and($post->author->is($author))->toBeTrue()
        ->and($category->posts()->sole()->is($post))->toBeTrue();
});
