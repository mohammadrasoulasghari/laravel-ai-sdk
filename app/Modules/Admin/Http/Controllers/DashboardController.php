<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function __invoke(Navigation $navigation, #[CurrentUser] Authenticatable $user): View
    {
        return view('admin::dashboard', [
            'sections' => array_values(array_filter(
                $navigation->visible(NavigationArea::Admin, $user),
                static fn (NavigationItem $item): bool => $item->route !== 'admin.dashboard',
            )),
        ]);
    }
}
