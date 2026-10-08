<x-layouts.guest :title="__('auth::auth.login_title')">
    <form method="POST" action="{{ route('login') }}" class="grid gap-5">
        @csrf

        <x-ui.input
            name="email"
            type="email"
            :label="__('validation.attributes.email')"
            required
            autofocus
            autocomplete="username"
        />
        <x-ui.input
            name="password"
            type="password"
            :label="__('validation.attributes.password')"
            required
            autocomplete="current-password"
        />

        <div class="flex items-center justify-between">
            <x-ui.checkbox name="remember" :label="__('auth::auth.remember_me')" />
            <a
                href="{{ route('password.request') }}"
                class="text-sm text-primary hover:underline"
                >{{ __('auth::auth.forgot_password') }}</a
            >
        </div>

        <x-ui.button type="submit" class="w-full">{{ __('auth::auth.login') }}</x-ui.button>

        <p class="text-center text-sm text-fg-muted">
            {{ __('auth::auth.no_account') }}
            <a href="{{ route('register') }}" class="text-primary hover:underline">{{ __('auth::auth.register') }}</a>
        </p>
    </form>
</x-layouts.guest>
