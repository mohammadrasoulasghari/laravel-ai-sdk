<?php

declare(strict_types=1);

namespace App\Modules\Blog\Database\Factories;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
final class PostFactory extends Factory
{
    /** @var class-string<Post> */
    protected $model = Post::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'category_id' => CategoryFactory::new(),
            'author_id' => User::factory(),
            'title' => mb_rtrim($title, '.'),
            'slug' => Slug::fromTitle($title.' '.fake()->unique()->numberBetween(1, 999999)),
            'excerpt' => fake()->sentence(12),
            'body' => fake()->paragraph(8)."\n\n".fake()->paragraph(8),
            'status' => PostStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function inCategory(Category $category): static
    {
        return $this->state(fn (): array => ['category_id' => $category->id]);
    }
}
