<?php

declare(strict_types=1);

use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

describe('index', function (): void {
    it('lists only published posts', function (): void {
        $live = Post::factory()->published()->create(['title' => 'Live post']);
        Post::factory()->create(['title' => 'Draft post']);
        Post::factory()->published()->create(['title' => 'Scheduled post', 'published_at' => now()->addDay()]);

        $this->get(route('blog.posts.index'))
            ->assertOk()
            ->assertSee(__('blog::posts.title'))
            ->assertSee('Live post')
            ->assertSee(route('blog.posts.show', $live->slug->value), false)
            ->assertDontSee('Draft post')
            ->assertDontSee('Scheduled post');
    });

    it('shows an empty state without posts', function (): void {
        $this->get(route('blog.posts.index'))
            ->assertOk()
            ->assertSee(__('blog::posts.empty'));
    });

    it('orders posts newest first', function (): void {
        Post::factory()->published()->create(['title' => 'Older', 'published_at' => now()->subDays(3)]);
        Post::factory()->published()->create(['title' => 'Newer', 'published_at' => now()->subDay()]);

        $this->get(route('blog.posts.index'))->assertSeeInOrder(['Newer', 'Older']);
    });

    it('filters by category', function (): void {
        $news = Category::factory()->create(['name' => 'News', 'slug' => 'news']);
        $tips = Category::factory()->create(['name' => 'Tips', 'slug' => 'tips']);
        Post::factory()->published()->inCategory($news)->create(['title' => 'Breaking news']);
        Post::factory()->published()->inCategory($tips)->create(['title' => 'Handy tip']);

        $this->get(route('blog.posts.index', ['filter' => ['category' => 'news']]))
            ->assertOk()
            ->assertSee('Breaking news')
            ->assertDontSee('Handy tip')
            ->assertViewHas('activeCategory', 'news');
    });

    it('links to every category filter', function (): void {
        Category::factory()->create(['name' => 'News', 'slug' => 'news']);

        $this->get(route('blog.posts.index'))
            ->assertSee('News')
            ->assertSee(e(route('blog.posts.index', ['filter' => ['category' => 'news']])), false);
    });

    it('paginates 15 posts per page and keeps the filter in the links', function (): void {
        $category = Category::factory()->create(['slug' => 'news']);
        Post::factory()->count(16)->published()->inCategory($category)->create();

        $this->get(route('blog.posts.index', ['filter' => ['category' => 'news']]))
            ->assertViewHas('posts', fn (LengthAwarePaginator $posts): bool => $posts->count() === 15
                && $posts->total() === 16
                && str_contains((string) $posts->nextPageUrl(), 'filter%5Bcategory%5D=news'));

        $this->get(route('blog.posts.index', ['filter' => ['category' => 'news'], 'page' => 2]))
            ->assertOk()
            ->assertViewHas('posts', fn (LengthAwarePaginator $posts): bool => $posts->count() === 1 && $posts->currentPage() === 2);
    });

    it('rejects an unknown sort value', function (): void {
        $this->get(route('blog.posts.index', ['sort' => 'created_at']))->assertSessionHasErrors('sort');
    });
});

describe('show', function (): void {
    it('shows a published post with its author and category', function (): void {
        $author = User::factory()->create(['name' => 'Jane Writer']);
        $category = Category::factory()->create(['name' => 'Guides']);
        $post = Post::factory()->published()->inCategory($category)->for($author, 'author')->create([
            'title' => 'Getting started',
            'body' => "## First steps\n\nRead **this**.",
        ]);

        $this->get(route('blog.posts.show', $post->slug->value))
            ->assertOk()
            ->assertSee('Getting started')
            ->assertSee(__('blog::posts.by_author', ['name' => 'Jane Writer']))
            ->assertSee('Guides')
            ->assertSee('<h2>First steps</h2>', false)
            ->assertSee('<strong>this</strong>', false);
    });

    it('strips raw HTML from the markdown body', function (): void {
        $post = Post::factory()->published()->create(['body' => "Safe text\n\n<script>alert(1)</script>"]);

        $this->get(route('blog.posts.show', $post->slug->value))
            ->assertOk()
            ->assertSee('Safe text')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('alert(1)', false);
    });

    it('serves posts with Persian slugs', function (): void {
        Post::factory()->published()->create(['title' => 'سلام دنیا', 'slug' => 'سلام-دنیا']);

        $this->get('/blog/سلام-دنیا')->assertOk()->assertSee('سلام دنیا');
    });

    it('returns 404 for posts that are not public', function (string $state): void {
        $post = match ($state) {
            'draft' => Post::factory()->create(),
            'scheduled' => Post::factory()->published()->create(['published_at' => now()->addDay()]),
        };

        $this->get(route('blog.posts.show', $post->slug->value))->assertNotFound();
    })->with(['draft', 'scheduled']);

    it('returns 404 for an unknown slug', function (): void {
        $this->get(route('blog.posts.show', 'does-not-exist'))->assertNotFound();
    });
});

describe('layout', function (): void {
    it('shows the Blog link in the main navigation', function (): void {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('blog.posts.index').'"', false)
            ->assertSee(__('blog::posts.nav'));
    });

    it('marks the Blog link active on blog pages', function (): void {
        $this->get(route('blog.posts.index'))
            ->assertSeeInOrder(['href="'.route('blog.posts.index').'"', 'aria-current="page"'], false);
    });

    it('renders the blog right-to-left in Persian', function (): void {
        Post::factory()->published()->create(['title' => 'نوشته فارسی']);

        $this->withSession(['locale' => 'fa'])
            ->get(route('blog.posts.index'))
            ->assertOk()
            ->assertSee('<html lang="fa" dir="rtl">', false)
            ->assertSee(__('blog::posts.title', locale: 'fa'))
            ->assertSee(__('blog::posts.read_more', locale: 'fa'))
            ->assertSee('نوشته فارسی');
    });
});
