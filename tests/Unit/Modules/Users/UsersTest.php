<?php

declare(strict_types=1);

use App\Modules\Roles\Contracts\RoleCatalog;
use App\Modules\Users\Data\UserFiltersData;
use App\Modules\Users\Enums\UserPermission;
use App\Modules\Users\Exceptions\CannotDeleteSelfException;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Shared\Auth\PermissionRegistry;

it('defaults filters to no search and 15 per page', function (): void {
    $filters = new UserFiltersData;

    expect($filters->search)->toBeNull()
        ->and($filters->perPage)->toBe(15);
});

it('describes the cannot delete self exception', function (): void {
    $exception = new CannotDeleteSelfException('x');

    expect($exception->errorCode())->toBe('users.cannot_delete_self')
        ->and($exception->status())->toBe(409)
        ->and(__($exception->translationKey()))->toBe('You cannot delete your own account.')
        ->and(__($exception->translationKey(), locale: 'fa'))->not->toBe(__($exception->translationKey(), locale: 'en'));
});

it('registers users.manage for admins', function (): void {
    expect(UserPermission::Manage->value)->toBe('users.manage')
        ->and(resolve(PermissionRegistry::class)->all())->toHaveKey('users.manage', ['admin']);
});

it('maps the update request to data', function (): void {
    $request = UpdateUserRequest::create('/admin/users/x', 'PUT', [
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'roles' => ['b' => 'admin', 'c' => 'member'],
    ]);

    $data = $request->toData();

    expect($data->name)->toBe('Jane')
        ->and($data->email)->toBe('jane@example.com')
        ->and($data->roles)->toBe(['admin', 'member']);
});

it('defaults to no roles when none are submitted', function (): void {
    expect(UpdateUserRequest::create('/admin/users/x', 'PUT', ['name' => 'Jane', 'email' => 'j@example.com'])->toData()->roles)->toBe([]);
});

it('only accepts roles from the catalog', function (): void {
    $catalog = new class implements RoleCatalog
    {
        public function options(): array
        {
            return ['admin' => 'Admin', 'member' => 'Member'];
        }
    };

    $rules = (new UpdateUserRequest)->rules($catalog);

    expect($rules)->toHaveKeys(['name', 'email', 'roles', 'roles.*'])
        ->and((string) $rules['roles.*'][1])->toBe('in:"admin","member"');
});
