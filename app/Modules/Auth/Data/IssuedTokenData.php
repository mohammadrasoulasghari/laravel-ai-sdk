<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use DateTimeInterface;
use SensitiveParameter;

final readonly class IssuedTokenData
{
    public function __construct(
        #[SensitiveParameter]
        public string $plainTextToken,
        public ?DateTimeInterface $expiresAt,
    ) {}
}
