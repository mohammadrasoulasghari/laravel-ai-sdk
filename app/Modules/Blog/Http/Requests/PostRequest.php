<?php

declare(strict_types=1);

namespace App\Modules\Blog\Http\Requests;

use App\Modules\Blog\Data\PostData;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Create and update share rules; authorization depends on whether a post is bound. */
final class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            ? $this->user()?->can('update', $post) ?? false
            : $this->user()?->can('create', Post::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $post = $this->route('post');

        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:'.Slug::MAX_LENGTH,
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && ! Slug::isValid($value)) {
                        $fail(__('validation.regex', ['attribute' => $attribute]));
                    }
                },
                Rule::unique(Post::class, 'slug')->ignore($post instanceof Post ? $post->id : null),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:100000'],
            'category_id' => ['required', 'string', Rule::exists(Category::class, 'id')],
        ];
    }

    public function toData(): PostData
    {
        $slug = $this->string('slug')->trim()->toString();
        $excerpt = $this->string('excerpt')->trim()->toString();

        return new PostData(
            title: $this->string('title')->trim()->toString(),
            body: $this->string('body')->toString(),
            categoryId: $this->string('category_id')->toString(),
            excerpt: $excerpt === '' ? null : $excerpt,
            slug: $slug === '' ? null : new Slug($slug),
        );
    }
}
