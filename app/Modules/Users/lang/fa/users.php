<?php

declare(strict_types=1);

return [
    'nav' => [
        'dashboard' => 'داشبورد',
        'users' => 'کاربران',
    ],
    'dashboard' => [
        'title' => 'داشبورد',
        'greeting' => ':name، خوش آمدید!',
        'body' => 'وارد شده‌اید. اولین ماژول خود را با ‎/feature یا php artisan make:module بسازید.',
        'admin_link' => 'ورود به پنل مدیریت',
    ],
    'admin' => [
        'title' => 'کاربران',
        'edit_title' => 'ویرایش :name',
        'search_placeholder' => 'جستجو بر اساس نام یا ایمیل',
        'name' => 'نام',
        'email' => 'ایمیل',
        'roles' => 'نقش‌ها',
        'joined' => 'تاریخ عضویت',
        'verified' => 'تأییدشده',
        'unverified' => 'تأییدنشده',
        'delete_confirm' => ':name حذف شود؟ توکن‌های API او نیز باطل می‌شوند.',
    ],
    'updated' => 'کاربر به‌روزرسانی شد.',
    'deleted' => 'کاربر حذف شد.',
    'errors' => [
        'cannot_delete_self' => 'نمی‌توانید حساب کاربری خودتان را حذف کنید.',
    ],
];
