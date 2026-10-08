<?php

declare(strict_types=1);

use App\Shared\Support\DateFormatter;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    config(['locales.display_timezone' => 'Asia/Tehran']);
});

it('formats Jalali dates for Persian', function (): void {
    app()->setLocale('fa');

    $date = CarbonImmutable::parse('2026-03-20 21:00:00', 'UTC'); // 2026-03-21 00:30 Tehran = 1405/01/01

    expect(resolve(DateFormatter::class)->date($date))->toBe('1405/01/01')
        ->and(resolve(DateFormatter::class)->dateTime($date))->toBe('1405/01/01 00:30');
});

it('formats Gregorian dates for English', function (): void {
    app()->setLocale('en');

    $date = CarbonImmutable::parse('2026-10-08 10:00:00', 'UTC');

    expect(resolve(DateFormatter::class)->date($date))->toBe('Oct 8, 2026')
        ->and(resolve(DateFormatter::class)->dateTime($date))->toBe('Oct 8, 2026 13:30');
});

it('formats relative dates in the current locale', function (): void {
    app()->setLocale('en');

    expect(resolve(DateFormatter::class)->forHumans(CarbonImmutable::now()->subHour()))->toBe('1 hour ago');
});
