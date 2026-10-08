<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Data\RegisterData;
use App\Modules\Auth\Exceptions\RegistrationClosedException;
use App\Modules\Settings\Contracts\SettingsStore;
use App\Modules\Settings\Enums\SettingKey;
use App\Modules\Users\Contracts\UserRegistrar;
use App\Modules\Users\Data\NewUserData;
use App\Modules\Users\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Self-service sign-up. Creating the user is delegated to the Users module through
 * its public contract; the Registered event triggers the verification email.
 */
final readonly class RegisterUser
{
    public function __construct(
        private UserRegistrar $users,
        private SettingsStore $settings,
        private Dispatcher $events,
    ) {}

    /** @throws RegistrationClosedException */
    public function handle(RegisterData $data): User
    {
        if (! $this->settings->boolean(SettingKey::RegistrationOpen)) {
            throw new RegistrationClosedException('Registration is disabled in settings.');
        }

        $user = $this->users->register(new NewUserData($data->name, $data->email, $data->password));

        $this->events->dispatch(new Registered($user));

        return $user;
    }
}
