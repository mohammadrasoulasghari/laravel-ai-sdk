<?php

declare(strict_types=1);

namespace App\Modules\Blog\Actions;

use App\Modules\Blog\Data\PostData;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;

final readonly class UpdatePost
{
    public function __construct(private GenerateUniqueSlug $slugs) {}

    public function handle(Post $post, PostData $data): Post
    {
        $post->fill([
            'category_id' => $data->categoryId,
            'title' => $data->title,
            'excerpt' => $data->excerpt,
            'body' => $data->body,
        ]);

        if ($data->slug instanceof Slug && ! $data->slug->equals($post->slug)) {
            $post->slug = $this->slugs->handle($data->slug, Post::class, $post->id);
        }

        $post->save();

        return $post;
    }
}
