<?php

declare(strict_types=1);

namespace App\Shared\Support;

use App\Shared\Localization\Calendar;
use App\Shared\Localization\Locales;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Hekmatinasser\Verta\Verta;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Date;

/**
 * Presentation-only date formatting: Jalali for "fa", Gregorian otherwise (ADR-0011).
 * Dates are stored in UTC and converted to the display timezone here.
 */
final readonly class DateFormatter
{
    public function __construct(
        private Locales $locales,
        private Config $config,
    ) {}

    public function date(DateTimeInterface $date): string
    {
        return $this->format($date, 'Y/m/d', 'M j, Y');
    }

    public function dateTime(DateTimeInterface $date): string
    {
        return $this->format($date, 'Y/m/d H:i', 'M j, Y H:i');
    }

    public function forHumans(DateTimeInterface $date): string
    {
        // locale() is a getter/setter returning static when given an argument.
        $localized = CarbonImmutable::instance($date)->locale($this->locales->current());

        return $localized instanceof CarbonInterface ? $localized->diffForHumans() : '';
    }

    private function format(DateTimeInterface $date, string $jalaliFormat, string $gregorianFormat): string
    {
        $local = Date::instance($date)->setTimezone($this->config->string('locales.display_timezone'));

        if ($this->locales->calendar() === Calendar::Jalali) {
            return (new Verta($local->toDateTime()))->format($jalaliFormat);
        }

        return $local->format($gregorianFormat);
    }
}
