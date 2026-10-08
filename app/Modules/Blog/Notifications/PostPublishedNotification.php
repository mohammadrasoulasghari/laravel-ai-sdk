<?php

declare(strict_types=1);

namespace App\Modules\Blog\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PostPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $title,
        public readonly string $slug,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject((string) __('blog::posts.mail.published_subject', ['title' => $this->title]))
            ->line((string) __('blog::posts.mail.published_line', ['title' => $this->title]))
            ->action((string) __('blog::posts.mail.view_post'), route('blog.posts.show', $this->slug));
    }
}
