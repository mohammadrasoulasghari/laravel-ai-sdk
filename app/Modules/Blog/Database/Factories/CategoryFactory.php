<?php

declare(strict_types=1);

namespace App\Modules\Blog\Database\Factories;

use App\Modules\Blog\Models\Category;
use App\Modules\Blog\ValueObjects\Slug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    /** @var class-string<Category> */
    protected $model = Category::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->word();
        $suffix = (string) fake()->unique()->numberBetween(1, 99999);

        return [
            'name' => ucfirst($name),
            'slug' => Slug::fromTitle($name.' '.$suffix),
        ];
    }
}
