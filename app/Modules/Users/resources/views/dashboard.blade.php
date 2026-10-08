<x-layouts.app :title="__('users::users.dashboard.title')">
    <x-ui.card>
        <p class="text-lg font-semibold">{{
            __('users::users.dashboard.greeting', [
                'name' => $user->name,
            ])
        }}</p>
        <p class="mt-2 text-fg-muted">{{ __('users::users.dashboard.body') }}</p>

        @can (\App\Shared\Auth\SystemAbility::AccessAdmin->value)
            <x-ui.button :href="route('admin.dashboard')" class="mt-6">
                {{ __('users::users.dashboard.admin_link') }}</x-ui.button
            >
        @endcan
    </x-ui.card>
</x-layouts.app>
