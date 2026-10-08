<?php

declare(strict_types=1);

namespace App\Modules\Roles\Http\Requests;

use App\Modules\Roles\Data\RolePermissionsData;
use App\Modules\Roles\Queries\PermissionNamesQuery;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

final class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role instanceof Role && ($this->user()?->can('update', $role) ?? false);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(PermissionNamesQuery $permissions): array
    {
        return [
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in($permissions->handle())],
        ];
    }

    public function toData(): RolePermissionsData
    {
        /** @var list<string> $permissions */
        $permissions = array_values($this->array('permissions'));

        return new RolePermissionsData($permissions);
    }
}
