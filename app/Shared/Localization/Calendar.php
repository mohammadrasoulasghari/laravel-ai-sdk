<?php

declare(strict_types=1);

namespace App\Shared\Localization;

enum Calendar: string
{
    case Gregorian = 'gregorian';
    case Jalali = 'jalali';
}
