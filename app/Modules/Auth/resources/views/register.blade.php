<x-layouts.guest :title="__('auth::auth.register_title')">
    <form method="POST" action="{{ route('register') }}" class="grid gap-5">
        @csrf

        <x-ui.input name="name" :label="__('validation.attributes.name')" required autofocus autocomplete="name" />
        <x-ui.input
            name="email"
            type="email"
            :label="__('validation.attributes.email')"
            required
            autocomplete="username"
        />
        <x-ui.input
            name="password"
            type="password"
            :label="__('validation.attributes.password')"
            required
            autocomplete="new-password"
        />
        <x-ui.input
            name="password_confirmation"
            type="password"
            :label="__('validation.attributes.password_confirmation')"
            required
            autocomplete="new-password"
        />

        <x-ui.button type="submit" class="w-full">{{ __('auth::auth.register') }}</x-ui.button>

        <p class="text-center text-sm text-fg-muted">
            {{ __('auth::auth.have_account') }}
            <a href="{{ route('login') }}" class="text-primary hover:underline">{{ __('auth::auth.login') }}</a>
        </p>
    </form>
</x-layouts.guest>
