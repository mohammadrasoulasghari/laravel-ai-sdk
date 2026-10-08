<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Modules\Users\Exceptions\CannotDeleteSelfException;
use App\Modules\Users\Models\User;
use Illuminate\Database\ConnectionInterface;

final readonly class DeleteUser
{
    public function __construct(private ConnectionInterface $db) {}

    /** @throws CannotDeleteSelfException */
    public function handle(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw new CannotDeleteSelfException('A user attempted to delete their own account from the admin panel.');
        }

        $this->db->transaction(static function () use ($user): void {
            $user->tokens()->delete();
            $user->delete();
        });
    }
}
