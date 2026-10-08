<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Data\PostData;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;
use App\Modules\Users\Models\User;

/** Creates a draft post. Publishing is a separate, explicit step (PublishPost). */
final readonly class CreatePost
{
    public function __construct(private GenerateUniqueSlug $slugs) {}

    public function handle(PostData $data, User $author): Post
    {
        $post = new Post([
            'category_id' => $data->categoryId,
            'author_id' => $author->id,
            'title' => $data->title,
            'slug' => $this->slugs->handle($data->slug ?? Slug::fromTitle($data->title), Post::class),
            'excerpt' => $data->excerpt,
            'body' => $data->body,
        ]);
        $post->status = PostStatus::Draft;
        $post->save();

        return $post;
    }
}
