<?php

declare(strict_types=1);

return [
    'nav' => 'بلاگ',
    'admin_nav' => 'نوشته‌ها',
    'title' => 'بلاگ',
    'admin_title' => 'نوشته‌ها',
    'new' => 'نوشته جدید',
    'create_title' => 'نوشته جدید',
    'edit_title' => 'ویرایش نوشته',
    'all_categories' => 'همه',
    'by_author' => 'نوشته :name',
    'read_more' => 'ادامه مطلب',
    'reading_time' => '{1} :minutes دقیقه مطالعه|[2,*] :minutes دقیقه مطالعه',
    'back_to_blog' => 'بازگشت به بلاگ',
    'search_placeholder' => 'جستجوی نوشته‌ها',
    'empty' => 'هنوز نوشته‌ای وجود ندارد.',
    'publish' => 'انتشار',
    'unpublish' => 'لغو انتشار',
    'view_public' => 'مشاهده در سایت',
    'delete_confirm' => '«:title» حذف شود؟ این عمل قابل بازگشت نیست.',
    'fields' => [
        'title' => 'عنوان',
        'slug' => 'نامک',
        'slug_hint' => 'برای ساخت خودکار از روی عنوان، خالی بگذارید.',
        'excerpt' => 'خلاصه',
        'body' => 'متن',
        'body_hint' => 'از Markdown پشتیبانی می‌شود. HTML خام حذف می‌شود.',
        'category' => 'دسته‌بندی',
        'status' => 'وضعیت',
        'published_at' => 'تاریخ انتشار',
        'author' => 'نویسنده',
    ],
    'status' => [
        'draft' => 'پیش‌نویس',
        'published' => 'منتشرشده',
    ],
    'created' => 'پیش‌نویس ایجاد شد.',
    'updated' => 'نوشته ذخیره شد.',
    'deleted' => 'نوشته حذف شد.',
    'published' => 'نوشته منتشر شد.',
    'unpublished' => 'نوشته به پیش‌نویس برگشت.',
    'errors' => [
        'already_published' => 'این نوشته قبلاً منتشر شده است.',
        'not_published' => 'این نوشته منتشر نشده است.',
    ],
    'mail' => [
        'published_subject' => 'نوشته «:title» شما منتشر شد',
        'published_line' => '«:title» منتشر شده است.',
        'view_post' => 'مشاهده نوشته',
    ],
];
