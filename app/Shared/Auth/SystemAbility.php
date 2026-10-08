<?php

declare(strict_types=1);

namespace App\Shared\Auth;

/**
 * Gate abilities for infrastructure tooling (Horizon, Telescope, Pulse, API docs).
 * Granted through the matching permission (see Roles module seeder).
 */
enum SystemAbility: string
{
    case ViewSystemTools = 'system.tools.view';
}
