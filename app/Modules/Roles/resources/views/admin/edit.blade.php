<x-layouts.admin :title="__('roles::roles.edit_title', ['role' => __('roles::roles.names.' . $role->name)])">
    <x-ui.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="grid gap-5">
            @csrf
            @method ('PUT')

            <fieldset>
                <legend class="mb-3 text-sm font-medium">{{ __('roles::roles.permissions') }}</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 font-mono text-sm">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission }}"
                                class="size-4 rounded border-border"
                                @checked (in_array($permission, old('permissions', $role->permissions->pluck('name')->all()), true))
                            />
                            {{ $permission }}
                        </label>
                    @endforeach
                </div>
                <x-ui.form-error name="permissions" />
            </fieldset>

            <div class="flex justify-end gap-2">
                <x-ui.button :href="route('admin.roles.index')" variant="secondary">{{ __('ui.cancel') }}</x-ui.button>
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.admin>
