<?php

declare(strict_types=1);

use App\Shared\Localization\Calendar;
use App\Shared\Localization\Direction;
use App\Shared\Localization\Locales;

it('knows direction and calendar per locale', function (): void {
    $locales = resolve(Locales::class);

    expect($locales->direction('fa'))->toBe(Direction::Rtl)
        ->and($locales->direction('en'))->toBe(Direction::Ltr)
        ->and($locales->calendar('fa'))->toBe(Calendar::Jalali)
        ->and($locales->calendar('en'))->toBe(Calendar::Gregorian)
        ->and($locales->isSupported('de'))->toBeFalse()
        ->and($locales->names())->toHaveKeys(['fa', 'en']);
});
