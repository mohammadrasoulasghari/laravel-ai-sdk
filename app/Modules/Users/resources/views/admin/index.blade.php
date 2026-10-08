<x-layouts.admin :title="__('users::users.admin.title')">
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 flex max-w-md gap-2" role="search">
        <x-ui.input
            name="search"
            type="search"
            :value="$search"
            :placeholder="__('users::users.admin.search_placeholder')"
            :aria-label="__('ui.search')"
        />
        <x-ui.button type="submit" variant="secondary">{{ __('ui.search') }}</x-ui.button>
    </form>

    @if ($users->isEmpty())
        <x-ui.empty-state />
    @else
        <x-ui.table>
            <x-slot:head>
                <th scope="col">{{ __('users::users.admin.name') }}</th>
                <th scope="col">{{ __('users::users.admin.email') }}</th>
                <th scope="col">{{ __('users::users.admin.roles') }}</th>
                <th scope="col">{{ __('users::users.admin.joined') }}</th>
                <th scope="col"><span class="sr-only">{{ __('ui.actions') }}</span></th>
            </x-slot:head>

            @foreach ($users as $user)
                <tr>
                    <td class="font-medium">{{ $user->name }}</td>
                    <td>
                        {{ $user->email }}
                        @if ($user->email_verified_at)
                            <x-ui.badge type="success">{{ __('users::users.admin.verified') }}</x-ui.badge>
                        @else
                            <x-ui.badge type="warning">{{ __('users::users.admin.unverified') }}</x-ui.badge>
                        @endif
                    </td>
                    <td>
                        @foreach ($user->roles as $role)
                            <x-ui.badge>{{ __('roles::roles.names.' . $role->name) }}</x-ui.badge>
                        @endforeach
                    </td>
                    <td><x-ui.date :value="$user->created_at" /></td>
                    <td class="text-end">
                        <div class="flex justify-end gap-2">
                            <x-ui.button :href="route('admin.users.edit', $user)" variant="ghost" size="sm">
                                {{ __('ui.edit') }}</x-ui.button
                            >
                            <x-ui.modal :title="__('ui.are_you_sure')">
                                <x-slot:trigger>
                                    <x-ui.button variant="ghost" size="sm" class="text-danger" x-on:click="show()">
                                        {{ __('ui.delete') }}</x-ui.button
                                    >
                                </x-slot:trigger>
                                {{
                                    __('users::users.admin.delete_confirm', [
                                        'name' => $user->name,
                                    ])
                                }}
                                <x-slot:footer>
                                    <x-ui.button variant="secondary" x-on:click="hide()">
                                        {{ __('ui.cancel') }}</x-ui.button
                                    >
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf
                                        @method ('DELETE')
                                        <x-ui.button type="submit" variant="danger">{{ __('ui.delete') }}</x-ui.button>
                                    </form>
                                </x-slot:footer>
                            </x-ui.modal>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.pagination :paginator="$users" />
    @endif
</x-layouts.admin>
