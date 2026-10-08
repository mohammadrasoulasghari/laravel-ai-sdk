<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Data\CategoryData;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\ValueObjects\Slug;

final readonly class UpdateCategory
{
    public function __construct(private GenerateUniqueSlug $slugs) {}

    public function handle(Category $category, CategoryData $data): Category
    {
        $category->name = $data->name;

        if ($data->slug instanceof Slug && ! $data->slug->equals($category->slug)) {
            $category->slug = $this->slugs->handle($data->slug, Category::class, $category->id);
        }

        $category->save();

        return $category;
    }
}
