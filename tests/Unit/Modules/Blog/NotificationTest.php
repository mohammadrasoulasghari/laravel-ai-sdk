<?php

declare(strict_types=1);

use App\Modules\Blog\Notifications\PostPublishedNotification;

it('is delivered by mail', function (): void {
    expect((new PostPublishedNotification('01POST', 'Hello', 'hello'))->via(new stdClass))->toBe(['mail']);
});

it('links the author to the public post', function (): void {
    $mail = (new PostPublishedNotification('01POST', 'Hello World', 'hello-world'))->toMail(new stdClass);

    expect($mail->subject)->toBe('Your post “Hello World” is live')
        ->and($mail->introLines)->toBe(['“Hello World” has been published.'])
        ->and($mail->actionText)->toBe(__('blog::posts.mail.view_post'))
        ->and($mail->actionUrl)->toBe(route('blog.posts.show', 'hello-world'));
});
