<?php

declare(strict_types=1);

namespace App\Modules\Admin\Providers;

use App\Shared\Auth\SystemAbility;
use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;
use App\Shared\Providers\ModuleServiceProvider;

/** Admin panel shell. Other modules add their own admin pages and nav items. */
final class AdminServiceProvider extends ModuleServiceProvider
{
    protected function bootModule(): void
    {
        $navigation = $this->app->make(Navigation::class);
        $navigation->add(new NavigationItem(NavigationArea::App, 'admin::admin.nav', 'admin.dashboard', SystemAbility::AccessAdmin->value, 'admin.*', order: 1000));
        $navigation->add(new NavigationItem(NavigationArea::Admin, 'admin::admin.dashboard', 'admin.dashboard', SystemAbility::AccessAdmin->value, 'admin.dashboard', order: 0));
    }
}
