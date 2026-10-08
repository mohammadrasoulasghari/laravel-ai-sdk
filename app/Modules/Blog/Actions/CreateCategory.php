<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Data\CategoryData;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\ValueObjects\Slug;

final readonly class CreateCategory
{
    public function __construct(private GenerateUniqueSlug $slugs) {}

    public function handle(CategoryData $data): Category
    {
        return Category::query()->create([
            'name' => $data->name,
            'slug' => $this->slugs->handle($data->slug ?? Slug::fromTitle($data->name), Category::class),
        ]);
    }
}
