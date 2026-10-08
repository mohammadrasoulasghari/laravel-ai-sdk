<?php

declare(strict_types=1);

namespace App\Modules\Blog\Models;

use App\Modules\Blog\Database\Factories\PostFactory;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\ValueObjects\Slug;
use App\Modules\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property-read string $id
 * @property string $category_id
 * @property string|null $author_id
 * @property string $title
 * @property Slug $slug
 * @property string|null $excerpt
 * @property string $body
 * @property PostStatus $status
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read string $body_html
 * @property-read int $reading_minutes
 * @property-read Category $category
 * @property-read User|null $author
 */
#[Table('blog_posts')]
#[Fillable(['category_id', 'author_id', 'title', 'slug', 'excerpt', 'body'])]
#[UseFactory(PostFactory::class)]
#[RouteKey('slug')]
final class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasUlids;

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Published and not scheduled in the future.
     * @param Builder<self> $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PostStatus::Published)->where('published_at', '<=', now());
    }

    /**
     * Markdown body rendered with raw HTML stripped and unsafe links disabled.
     *
     * @return Attribute<string, never>
     */
    protected function bodyHtml(): Attribute
    {
        return Attribute::get(fn (): string => Str::markdown($this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]))->shouldCache();
    }

    /**
     * Estimated reading time at ~200 words per minute (min. 1).
     *
     * @return Attribute<int<1, max>, never>
     */
    protected function readingMinutes(): Attribute
    {
        return Attribute::get(fn (): int => max(1, (int) ceil(count(preg_split('/\s+/u', mb_trim(strip_tags($this->body_html)), -1, PREG_SPLIT_NO_EMPTY) ?: []) / 200)));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'slug' => Slug::class,
            'status' => PostStatus::class,
            'published_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
