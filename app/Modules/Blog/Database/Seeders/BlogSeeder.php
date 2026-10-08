<?php

declare(strict_types=1);

namespace App\Modules\Blog\Database\Seeders;

use App\Modules\Blog\Database\Factories\CategoryFactory;
use App\Modules\Blog\Database\Factories\PostFactory;
use App\Modules\Blog\Models\Category;
use App\Modules\Users\Models\User;
use Illuminate\Database\Seeder;

/** Demo content for local development only. */
final class BlogSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $author = User::query()->firstOrFail();

        CategoryFactory::new()->count(3)->create()->each(static function (Category $category) use ($author): void {
            PostFactory::new()->count(4)->published()->inCategory($category)->create(['author_id' => $author->id]);
            PostFactory::new()->inCategory($category)->create(['author_id' => $author->id]);
        });
    }
}
