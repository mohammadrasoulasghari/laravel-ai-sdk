<?php

declare(strict_types=1);

namespace App\Shared\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/** Local-only page showing every UI kit component (see the blade-component skill). */
final class StyleGuideController
{
    public function __invoke(Request $request): View
    {
        /** @var LengthAwarePaginator<int, int> $paginator */
        $paginator = new LengthAwarePaginator(range(1, 10), 120, 10, 3, ['path' => $request->url()]);

        return view('styleguide', [
            'paginator' => $paginator,
            'now' => CarbonImmutable::now(),
            'options' => [
                1 => __('ui.styleguide.sample_option', ['number' => 1]),
                2 => __('ui.styleguide.sample_option', ['number' => 2]),
            ],
        ]);
    }
}
