<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use SensitiveParameter;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
        public bool $remember,
        public string $ip,
    ) {}

    /** Rate-limit bucket: one per email + IP pair. */
    public function throttleKey(): string
    {
        return 'login:'.hash('xxh128', mb_strtolower($this->email).'|'.$this->ip);
    }
}
