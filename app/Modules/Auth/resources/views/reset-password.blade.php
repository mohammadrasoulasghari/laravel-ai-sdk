<x-layouts.guest :title="__('auth::auth.reset_title')">
    <form method="POST" action="{{ route('password.store') }}" class="grid gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}" />

        <x-ui.input
            name="email"
            type="email"
            :label="__('validation.attributes.email')"
            :value="$email"
            required
            autocomplete="username"
        />
        <x-ui.input
            name="password"
            type="password"
            :label="__('validation.attributes.password')"
            required
            autofocus
            autocomplete="new-password"
        />
        <x-ui.input
            name="password_confirmation"
            type="password"
            :label="__('validation.attributes.password_confirmation')"
            required
            autocomplete="new-password"
        />

        <x-ui.button type="submit" class="w-full">{{ __('auth::auth.reset_password') }}</x-ui.button>
    </form>
</x-layouts.guest>
