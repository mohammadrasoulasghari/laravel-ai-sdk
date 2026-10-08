<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Data\IssuedTokenData;
use App\Modules\Auth\Data\IssueTokenData;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use App\Modules\Users\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Hashing\Hasher;

/** Exchanges credentials for a Sanctum personal access token (mobile / CLI clients). */
final readonly class IssueApiToken
{
    public function __construct(
        private Hasher $hasher,
        private RateLimiter $limiter,
        private Config $config,
    ) {}

    /**
     * @throws InvalidCredentialsException
     * @throws TooManyLoginAttemptsException
     */
    public function handle(IssueTokenData $data): IssuedTokenData
    {
        $key = $data->throttleKey();

        if ($this->limiter->tooManyAttempts($key, AuthenticateUser::MAX_ATTEMPTS)) {
            throw new TooManyLoginAttemptsException($this->limiter->availableIn($key));
        }

        $user = User::query()->where('email', mb_strtolower($data->email))->first();

        if ($user === null || ! $this->hasher->check($data->password, $user->password)) {
            $this->limiter->hit($key, AuthenticateUser::DECAY_SECONDS);

            throw new InvalidCredentialsException('Invalid API token credentials.');
        }

        $this->limiter->clear($key);

        $minutes = $this->config->get('sanctum.expiration');
        $expiresAt = is_numeric($minutes) ? now()->addMinutes((int) $minutes) : null;

        $token = $user->createToken($data->deviceName, ['*'], $expiresAt);

        return new IssuedTokenData($token->plainTextToken, $expiresAt);
    }
}
