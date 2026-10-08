<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Data\LoginData;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use LogicException;

/**
 * Session login with brute-force protection: MAX_ATTEMPTS per email+IP per DECAY_SECONDS.
 */
final readonly class AuthenticateUser
{
    public const int MAX_ATTEMPTS = 5;

    public const int DECAY_SECONDS = 60;

    public function __construct(
        private AuthFactory $auth,
        private RateLimiter $limiter,
    ) {}

    /**
     * @throws InvalidCredentialsException
     * @throws TooManyLoginAttemptsException
     */
    public function handle(LoginData $data): void
    {
        $key = $data->throttleKey();

        if ($this->limiter->tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new TooManyLoginAttemptsException($this->limiter->availableIn($key));
        }

        if (! $this->guard()->attempt(['email' => mb_strtolower($data->email), 'password' => $data->password], $data->remember)) {
            $this->limiter->hit($key, self::DECAY_SECONDS);

            throw new InvalidCredentialsException('Invalid login credentials.');
        }

        $this->limiter->clear($key);
    }

    private function guard(): StatefulGuard
    {
        $guard = $this->auth->guard('web');

        throw_unless($guard instanceof StatefulGuard, LogicException::class, 'The web guard must be stateful.');

        return $guard;
    }
}
