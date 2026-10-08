<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
| Scheduled tasks (run by the `scheduler` container: php artisan schedule:work).
| Use ->onOneServer() for anything that must not run twice when scaled out.
*/

// Delete Sanctum tokens that expired more than 24 hours ago.
Schedule::command('sanctum:prune-expired --hours=24')->daily()->onOneServer();

// Pulse and Horizon housekeeping.
Schedule::command('horizon:snapshot')->everyFiveMinutes()->onOneServer();
Schedule::command('pulse:clear --type=all --force')->weekly()->onOneServer();
