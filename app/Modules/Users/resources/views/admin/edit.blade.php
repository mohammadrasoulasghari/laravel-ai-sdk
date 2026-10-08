<x-layouts.admin :title="__('users::users.admin.edit_title', ['name' => $user->name])">
    <x-ui.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-5">
            @csrf
            @method ('PUT')

            <x-ui.input
                name="name"
                :label="__('validation.attributes.name')"
                :value="$user->name"
                required
                autocomplete="name"
            />
            <x-ui.input
                name="email"
                type="email"
                :label="__('validation.attributes.email')"
                :value="$user->email"
                required
                autocomplete="email"
            />

            <fieldset>
                <legend class="mb-2 text-sm font-medium">{{ __('users::users.admin.roles') }}</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($roleOptions as $value => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $value }}"
                                class="size-4 rounded border-border"
                                @checked (in_array($value, old('roles', $user->roles->pluck('name')->all()), true))
                            />
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <x-ui.form-error name="roles" />
            </fieldset>

            <div class="flex justify-end gap-2">
                <x-ui.button :href="route('admin.users.index')" variant="secondary">{{ __('ui.cancel') }}</x-ui.button>
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.admin>
