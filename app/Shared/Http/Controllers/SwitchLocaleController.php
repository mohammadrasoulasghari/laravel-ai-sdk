<?php

declare(strict_types=1);

namespace App\Shared\Http\Controllers;

use App\Shared\Http\Middleware\SetLocale;
use App\Shared\Localization\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SwitchLocaleController
{
    public function __construct(private Locales $locales) {}

    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        throw_unless($this->locales->isSupported($locale), NotFoundHttpException::class);

        $request->session()->put(SetLocale::SESSION_KEY, $locale);

        return back();
    }
}
