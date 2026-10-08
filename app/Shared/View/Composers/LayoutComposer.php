<?php

declare(strict_types=1);

namespace App\Shared\View\Composers;

use App\Shared\Localization\Locales;
use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\View\View;

/**
 * Supplies layouts with ready-to-render data so layout views contain no logic.
 */
final readonly class LayoutComposer
{
    public function __construct(
        private Navigation $navigation,
        private Locales $locales,
        private Auth $auth,
    ) {}

    public function compose(View $view): void
    {
        $user = $this->auth->guard()->user();

        $view->with([
            'appNavigation' => $this->navigation->visible(NavigationArea::App, $user),
            'adminNavigation' => $this->navigation->visible(NavigationArea::Admin, $user),
            'locales' => $this->locales->names(),
            'currentLocale' => $this->locales->current(),
            'direction' => $this->locales->direction()->value,
            'currentUser' => $user,
        ]);
    }
}
