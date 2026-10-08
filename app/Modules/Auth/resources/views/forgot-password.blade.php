<x-layouts.guest :title="__('auth::auth.forgot_title')">
    <p class="mb-5 text-sm text-fg-muted">{{ __('auth::auth.forgot_body') }}</p>

    <form method="POST" action="{{ route('password.email') }}" class="grid gap-5">
        @csrf

        <x-ui.input
            name="email"
            type="email"
            :label="__('validation.attributes.email')"
            required
            autofocus
            autocomplete="username"
        />

        <x-ui.button type="submit" class="w-full">{{ __('auth::auth.send_reset_link') }}</x-ui.button>

        <a
            href="{{ route('login') }}"
            class="text-center text-sm text-primary hover:underline"
            >{{ __('auth::auth.back_to_login') }}</a
        >
    </form>
</x-layouts.guest>
