<?php

declare(strict_types=1);

namespace App\Shared\View\Components;

use App\Shared\Support\DateFormatter;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * <x-ui.date :value="$post->published_at" /> → <time datetime="...">۱۴۰۵/۰۷/۱۶</time>
 */
final class Date extends Component
{
    public readonly string $formatted;

    public readonly string $iso;

    public function __construct(
        DateFormatter $formatter,
        DateTimeInterface $value,
        bool $withTime = false,
        bool $relative = false,
    ) {
        $this->iso = $value->format(DateTimeInterface::ATOM);
        $this->formatted = match (true) {
            $relative => $formatter->forHumans($value),
            $withTime => $formatter->dateTime($value),
            default => $formatter->date($value),
        };
    }

    public function render(): View
    {
        return view('components.ui.date');
    }
}
