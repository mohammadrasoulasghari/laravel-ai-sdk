<x-layouts.guest :title="__('auth::auth.verify_title')">
    <p class="mb-5 text-sm text-fg-muted">{{ __('auth::auth.verify_body') }}</p>

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit">{{ __('auth::auth.resend_verification') }}</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost">{{ __('auth::auth.logout') }}</x-ui.button>
        </form>
    </div>
</x-layouts.guest>
