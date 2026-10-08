<?php

declare(strict_types=1);

use App\Modules\Blog\Data\CategoryData;
use App\Modules\Blog\Data\PostData;
use App\Modules\Blog\Data\PostFiltersData;
use App\Modules\Blog\Enums\PostSort;
use App\Modules\Blog\Events\PostPublished;
use App\Modules\Blog\Http\Requests\PostIndexRequest;
use App\Modules\Blog\Http\Requests\PostRequest;
use App\Modules\Blog\ValueObjects\Slug;

describe('DTOs', function (): void {
    it('leaves optional post fields empty by default', function (): void {
        $data = new PostData(title: 'Hello', body: 'Body', categoryId: '01CATEGORY');

        expect($data->title)->toBe('Hello')
            ->and($data->body)->toBe('Body')
            ->and($data->categoryId)->toBe('01CATEGORY')
            ->and($data->excerpt)->toBeNull()
            ->and($data->slug)->toBeNull();
    });

    it('carries an explicit slug for categories', function (): void {
        $data = new CategoryData('News', new Slug('news'));

        expect($data->name)->toBe('News')
            ->and($data->slug?->value)->toBe('news')
            ->and((new CategoryData('News'))->slug)->toBeNull();
    });

    it('defaults filters to newest first, 15 per page, no search', function (): void {
        $filters = new PostFiltersData;

        expect($filters->search)->toBeNull()
            ->and($filters->categorySlug)->toBeNull()
            ->and($filters->sort)->toBe(PostSort::NewestFirst)
            ->and($filters->perPage)->toBe(15);
    });

    it('exposes ids on the PostPublished event', function (): void {
        $event = new PostPublished('01POST', '01AUTHOR', '2026-10-08T10:00:00+00:00');

        expect($event->postId)->toBe('01POST')
            ->and($event->authorId)->toBe('01AUTHOR')
            ->and($event->publishedAt)->toBe('2026-10-08T10:00:00+00:00');
    });
});

describe('request mapping', function (): void {
    it('maps the post request to data, trimming input', function (): void {
        $data = PostRequest::create('/admin/blog/posts', 'POST', [
            'title' => '  Hello World  ',
            'slug' => ' custom-slug ',
            'excerpt' => ' Short ',
            'body' => "  # Heading\n",
            'category_id' => '01CATEGORY',
        ])->toData();

        expect($data->title)->toBe('Hello World')
            ->and($data->slug?->value)->toBe('custom-slug')
            ->and($data->excerpt)->toBe('Short')
            ->and($data->body)->toBe("  # Heading\n")
            ->and($data->categoryId)->toBe('01CATEGORY');
    });

    it('turns blank slug and excerpt into null', function (): void {
        $data = PostRequest::create('/admin/blog/posts', 'POST', [
            'title' => 'Hello',
            'slug' => '   ',
            'excerpt' => '',
            'body' => 'Body',
            'category_id' => '01CATEGORY',
        ])->toData();

        expect($data->slug)->toBeNull()
            ->and($data->excerpt)->toBeNull();
    });

    it('maps listing query parameters to filters', function (): void {
        $filters = PostIndexRequest::create('/api/v1/blog/posts', 'GET', [
            'filter' => ['search' => ' laravel ', 'category' => 'news'],
            'sort' => 'title',
            'per_page' => '30',
        ])->toData();

        expect($filters->search)->toBe('laravel')
            ->and($filters->categorySlug)->toBe('news')
            ->and($filters->sort)->toBe(PostSort::TitleAsc)
            ->and($filters->perPage)->toBe(30);
    });

    it('falls back to default filters for an empty query', function (): void {
        $filters = PostIndexRequest::create('/api/v1/blog/posts', 'GET', ['filter' => ['search' => '  ']])->toData();

        expect($filters->search)->toBeNull()
            ->and($filters->categorySlug)->toBeNull()
            ->and($filters->sort)->toBe(PostSort::NewestFirst)
            ->and($filters->perPage)->toBe(config('starter.pagination.per_page'));
    });
});
