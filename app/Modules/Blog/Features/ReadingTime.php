<?php

declare(strict_types=1);

namespace App\Modules\Blog\Features;

use App\Shared\Auth\SystemAbility;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Pennant feature flag: show "N min read" on posts. Rolled out to staff first
 * (users with admin.access). Enable for someone with Feature::for($user)->activate(ReadingTime::class);
 * roll out to all by changing resolve() and running `php artisan pennant:purge`.
 */
final readonly class ReadingTime
{
    public function __construct(private Gate $gate) {}

    public function resolve(?Authenticatable $user): bool
    {
        return $user instanceof Authenticatable && $this->gate->forUser($user)->allows(SystemAbility::AccessAdmin->value);
    }
}
