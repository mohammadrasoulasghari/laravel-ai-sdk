<?php

declare(strict_types=1);

namespace App\Shared\Auth;

/**
 * App-wide permissions not owned by a single module. Registered in SharedServiceProvider.
 */
enum SystemAbility: string
{
    /** Enter /admin (the "admin" middleware group checks it). */
    case AccessAdmin = 'admin.access';

    /** Horizon, Telescope, Pulse and the API docs outside local. */
    case ViewSystemTools = 'system.tools.view';
}
