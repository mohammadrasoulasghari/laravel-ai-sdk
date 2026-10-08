<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use SensitiveParameter;

/** Input for creating a user. Part of the Users public API (used by Auth). */
final readonly class NewUserData
{
    public function __construct(
        public string $name,
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}
}
