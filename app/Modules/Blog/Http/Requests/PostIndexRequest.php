<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Requests;

use App\Modules\Blog\Data\PostFiltersData;
use App\Modules\Blog\Enums\PostSort;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Listing query contract (docs/api.md): ?filter[search]=&filter[category]=&sort=-published_at&per_page=15&page=1
 */
final class PostIndexRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(Config $config): array
    {
        return [
            'filter' => ['sometimes', 'array:search,category'],
            'filter.search' => ['nullable', 'string', 'max:100'],
            'filter.category' => ['nullable', 'string', 'max:200'],
            'sort' => ['nullable', Rule::enum(PostSort::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.$config->integer('starter.pagination.max_per_page')],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toData(): PostFiltersData
    {
        $search = $this->string('filter.search')->trim()->toString();
        $category = $this->string('filter.category')->trim()->toString();

        return new PostFiltersData(
            search: $search === '' ? null : $search,
            categorySlug: $category === '' ? null : $category,
            sort: $this->enum('sort', PostSort::class) ?? PostSort::NewestFirst,
            perPage: $this->integer('per_page', config()->integer('starter.pagination.per_page')),
        );
    }
}
