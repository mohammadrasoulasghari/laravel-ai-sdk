<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use SensitiveParameter;

final readonly class RegisterData
{
    public function __construct(
        public string $name,
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}
}
