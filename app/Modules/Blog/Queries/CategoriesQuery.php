<?php

declare(strict_types=1);

namespace App\Modules\Blog\Queries;

use App\Modules\Blog\Models\Category;
use Illuminate\Database\Eloquent\Collection;

final readonly class CategoriesQuery
{
    /** @return Collection<int, Category> */
    public function handle(): Collection
    {
        return Category::query()->withCount('posts')->orderBy('name')->get();
    }

    /** @return array<string, string> id => name, for <x-ui.select> */
    public function options(): array
    {
        $options = [];

        foreach (Category::query()->orderBy('name')->get(['id', 'name']) as $category) {
            $options[$category->id] = $category->name;
        }

        return $options;
    }
}
