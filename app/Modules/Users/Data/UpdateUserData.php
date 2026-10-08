<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

final readonly class UpdateUserData
{
    /**
     * @param  list<string>  $roles  Role names to sync.
     */
    public function __construct(
        public string $name,
        public string $email,
        public array $roles,
    ) {}
}
