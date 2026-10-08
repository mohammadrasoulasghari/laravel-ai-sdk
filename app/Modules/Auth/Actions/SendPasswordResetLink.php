<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use Illuminate\Contracts\Auth\PasswordBroker;

/**
 * Sends a reset link if the account exists. The result is intentionally not exposed
 * so responses never reveal whether an email is registered.
 */
final readonly class SendPasswordResetLink
{
    public function __construct(private PasswordBroker $broker) {}

    public function handle(string $email): void
    {
        $this->broker->sendResetLink(['email' => mb_strtolower($email)]);
    }
}
