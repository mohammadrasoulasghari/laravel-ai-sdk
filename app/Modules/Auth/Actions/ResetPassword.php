<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Data\ResetPasswordData;
use App\Modules\Auth\Exceptions\PasswordResetFailedException;
use App\Modules\Users\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

final readonly class ResetPassword
{
    public function __construct(
        private PasswordBroker $broker,
        private Dispatcher $events,
    ) {}

    /** @throws PasswordResetFailedException */
    public function handle(ResetPasswordData $data): void
    {
        $status = $this->broker->reset(
            ['email' => mb_strtolower($data->email), 'password' => $data->password, 'token' => $data->token],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                $user->tokens()->delete();

                $this->events->dispatch(new PasswordReset($user));
            },
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw new PasswordResetFailedException(is_string($status) ? $status : PasswordBroker::INVALID_TOKEN);
        }
    }
}
