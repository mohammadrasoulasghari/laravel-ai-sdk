<?php

declare(strict_types=1);

use App\Modules\Blog\Actions\CreateCategory;
use App\Modules\Blog\Actions\DeleteCategory;
use App\Modules\Blog\Actions\UpdateCategory;
use App\Modules\Blog\Data\CategoryData;
use App\Modules\Blog\Exceptions\CategoryHasPostsException;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;

describe('CreateCategory', function (): void {
    it('generates the slug from the name', function (string $name, string $slug): void {
        $category = resolve(CreateCategory::class)->handle(new CategoryData($name));

        expect($category->name)->toBe($name)
            ->and($category->slug->value)->toBe($slug);
    })->with([
        'english' => ['Release Notes', 'release-notes'],
        'persian' => ['اخبار فناوری', 'اخبار-فناوری'],
    ]);

    it('uses an explicit slug and suffixes duplicates', function (): void {
        $create = resolve(CreateCategory::class);

        $first = $create->handle(new CategoryData('News', new Slug('news')));
        $second = $create->handle(new CategoryData('More news', new Slug('news')));

        expect($first->slug->value)->toBe('news')
            ->and($second->slug->value)->toBe('news-2');
    });
});

describe('UpdateCategory', function (): void {
    it('renames without touching the slug when none is given', function (): void {
        $category = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);

        resolve(UpdateCategory::class)->handle($category, new CategoryData('New'));

        $category->refresh();

        expect($category->name)->toBe('New')
            ->and($category->slug->value)->toBe('old');
    });

    it('changes the slug, suffixing it when taken', function (): void {
        Category::factory()->create(['slug' => 'taken']);
        $category = Category::factory()->create(['slug' => 'mine']);

        resolve(UpdateCategory::class)->handle($category, new CategoryData('Renamed', new Slug('taken')));

        expect($category->refresh()->slug->value)->toBe('taken-2');
    });

    it('keeps its own slug as is', function (): void {
        $category = Category::factory()->create(['slug' => 'mine']);

        resolve(UpdateCategory::class)->handle($category, new CategoryData('Renamed', new Slug('mine')));

        expect($category->refresh()->slug->value)->toBe('mine');
    });
});

describe('DeleteCategory', function (): void {
    it('deletes an empty category', function (): void {
        $category = Category::factory()->create();

        resolve(DeleteCategory::class)->handle($category);

        $this->assertModelMissing($category);
    });

    it('refuses to delete a category that still has posts', function (): void {
        $category = Category::factory()->create();
        Post::factory()->count(2)->inCategory($category)->create();

        expect(fn (): null => resolve(DeleteCategory::class)->handle($category))
            ->toThrow(function (CategoryHasPostsException $e): void {
                expect($e->postCount)->toBe(2);
            });

        $this->assertModelExists($category);
    });
});
