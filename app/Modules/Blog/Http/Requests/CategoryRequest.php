<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Requests;

use App\Modules\Blog\Data\CategoryData;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\ValueObjects\Slug;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category instanceof Category
            ? $this->user()?->can('update', $category) ?? false
            : $this->user()?->can('create', Category::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'nullable',
                'string',
                'max:'.Slug::MAX_LENGTH,
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && ! Slug::isValid($value)) {
                        $fail(__('validation.regex', ['attribute' => $attribute]));
                    }
                },
                Rule::unique(Category::class, 'slug')->ignore($category instanceof Category ? $category->id : null),
            ],
        ];
    }

    public function toData(): CategoryData
    {
        $slug = $this->string('slug')->trim()->toString();

        return new CategoryData(
            name: $this->string('name')->trim()->toString(),
            slug: $slug === '' ? null : new Slug($slug),
        );
    }
}
