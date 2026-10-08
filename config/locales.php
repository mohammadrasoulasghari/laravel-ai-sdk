<?php

declare(strict_types=1);

/*
| Supported UI locales. The first matching locale wins in this order:
| session (user switched) → Accept-Language (API) → app.locale.
| "dir" drives <html dir>, "calendar" drives date rendering (jalali|gregorian).
*/
return [
    'supported' => [
        'fa' => ['name' => 'فارسی', 'dir' => 'rtl', 'calendar' => 'jalali'],
        'en' => ['name' => 'English', 'dir' => 'ltr', 'calendar' => 'gregorian'],
    ],

    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Tehran'),
];
