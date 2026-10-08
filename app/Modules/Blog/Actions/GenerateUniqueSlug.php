<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\ValueObjects\Slug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Returns $slug, or $slug-2, $slug-3 … until it is unused in the given model's table.
 */
final readonly class GenerateUniqueSlug
{
    /** @param  class-string<Model>  $model */
    public function handle(Slug $slug, string $model, ?string $ignoreId = null): Slug
    {
        $candidate = $slug;

        for ($suffix = 2; $this->taken($model, $candidate, $ignoreId); $suffix++) {
            $candidate = $slug->withSuffix($suffix);
        }

        return $candidate;
    }

    /** @param  class-string<Model>  $model */
    private function taken(string $model, Slug $slug, ?string $ignoreId): bool
    {
        return $model::query()
            ->where('slug', $slug->value)
            ->when($ignoreId !== null, static fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
