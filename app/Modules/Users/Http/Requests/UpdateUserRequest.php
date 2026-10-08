<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Modules\Roles\Contracts\RoleCatalog;
use App\Modules\Roles\Enums\Role;
use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && ($this->user()?->can('update', $user) ?? false);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(RoleCatalog $roles): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user instanceof User ? $user->id : null)],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::in($this->assignableRoles($roles))],
        ];
    }

    public function toData(): UpdateUserData
    {
        /** @var list<string> $roles */
        $roles = array_values($this->array('roles'));

        return new UpdateUserData(
            name: $this->string('name')->toString(),
            email: $this->string('email')->toString(),
            roles: $roles,
        );
    }

    /**
     * Only super admins may grant (or keep granting) the super-admin role.
     *
     * @return list<string>
     */
    private function assignableRoles(RoleCatalog $roles): array
    {
        $names = array_keys($roles->options());

        if ($this->user()?->hasRole(Role::SuperAdmin->value) === true) {
            return $names;
        }

        return array_values(array_filter($names, static fn (string $name): bool => $name !== Role::SuperAdmin->value));
    }
}
