<?php

declare(strict_types=1);

use App\Modules\Blog\Features\ReadingTime;
use App\Modules\Blog\Models\Post;
use App\Modules\Roles\Enums\Role;
use Laravel\Pennant\Feature;

use function Tests\userWithRole;

it('shows reading time to staff only', function (): void {
    $post = Post::factory()->published()->create(['body' => str_repeat('word ', 450)]);

    $this->get(route('blog.posts.show', $post->slug->value))->assertDontSee('3 min read');

    $this->actingAs(userWithRole(Role::Member->value))
        ->get(route('blog.posts.show', $post->slug->value))->assertDontSee('3 min read');

    $this->actingAs(userWithRole(Role::Admin->value))
        ->get(route('blog.posts.show', $post->slug->value))->assertSee('3 min read');
});

it('can be activated for a specific user', function (): void {
    $member = userWithRole(Role::Member->value);
    Feature::for($member)->activate(ReadingTime::class);
    $post = Post::factory()->published()->create(['body' => 'short']);

    $this->actingAs($member)
        ->get(route('blog.posts.show', $post->slug->value))->assertSee('1 min read');
});
