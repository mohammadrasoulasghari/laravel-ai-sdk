<x-layouts.admin :title="__('roles::roles.title')">
    <x-ui.table>
        <x-slot:head>
            <th scope="col">{{ __('roles::roles.role') }}</th>
            <th scope="col">{{ __('roles::roles.users') }}</th>
            <th scope="col">{{ __('roles::roles.permissions') }}</th>
            <th scope="col"><span class="sr-only">{{ __('ui.actions') }}</span></th>
        </x-slot:head>

        @foreach ($roles as $role)
            <tr>
                <td class="font-medium">{{ __('roles::roles.names.' . $role->name) }}</td>
                <td>{{ $role->users_count }}</td>
                <td>
                    @if ($role->name === \App\Modules\Roles\Enums\Role::SuperAdmin->value)
                        <x-ui.badge type="info">{{ __('roles::roles.all_permissions') }}</x-ui.badge>
                    @else
                        <div class="flex flex-wrap gap-1">
                            @foreach ($role->permissions as $permission)
                                <x-ui.badge>{{ $permission->name }}</x-ui.badge>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="text-end">
                    @if ($role->name !== \App\Modules\Roles\Enums\Role::SuperAdmin->value)
                        <x-ui.button :href="route('admin.roles.edit', $role)" variant="ghost" size="sm">
                            {{ __('ui.edit') }}</x-ui.button
                        >
                    @endif
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
