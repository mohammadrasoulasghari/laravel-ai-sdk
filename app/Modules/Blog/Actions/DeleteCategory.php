<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Exceptions\CategoryHasPostsException;
use App\Modules\Blog\Models\Category;

final readonly class DeleteCategory
{
    /** @throws CategoryHasPostsException */
    public function handle(Category $category): void
    {
        $count = $category->posts()->count();

        if ($count > 0) {
            throw new CategoryHasPostsException($count);
        }

        $category->delete();
    }
}
