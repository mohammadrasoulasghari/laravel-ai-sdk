<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Data\PostData;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;
use App\Modules\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

/** Creates a draft post. Publishing is a separate, explicit step (PublishPost). */
final readonly class CreatePost
{
    private const int MAX_SLUG_ATTEMPTS = 3;

    public function __construct(private GenerateUniqueSlug $slugs) {}

    public function handle(PostData $data, User $author, int $attempt = 1): Post
    {
        $base = $data->slug ?? Slug::fromTitle($data->title);

        $post = new Post([
            'category_id' => $data->categoryId,
            'author_id' => $author->id,
            'title' => $data->title,
            'slug' => $this->slugs->handle($attempt === 1 ? $base : $base->withSuffix(random_int(100, 999)), Post::class),
            'excerpt' => $data->excerpt,
            'body' => $data->body,
        ]);
        $post->status = PostStatus::Draft;

        try {
            $post->save();
        } catch (UniqueConstraintViolationException $uniqueConstraintViolationException) {
            // Another request took the same slug between our check and insert.
            if ($attempt >= self::MAX_SLUG_ATTEMPTS) {
                throw $uniqueConstraintViolationException;
            }

            return $this->handle($data, $author, $attempt + 1);
        }

        return $post;
    }
}
