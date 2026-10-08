<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use SensitiveParameter;

final readonly class IssueTokenData
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
        public string $deviceName,
        public string $ip,
    ) {}

    public function throttleKey(): string
    {
        return 'api-token:'.hash('xxh128', mb_strtolower($this->email).'|'.$this->ip);
    }
}
