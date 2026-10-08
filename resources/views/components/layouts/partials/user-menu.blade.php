{{-- Auth links for the header. Routes are provided by the Auth module. --}}
@props (['user'])

@if ($user)
    <span class="hidden text-sm text-fg-muted sm:inline">{{ $user->name }}</span>
    @if (Route::has('logout'))
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" size="sm">{{ __('auth::auth.logout') }}</x-ui.button>
        </form>
    @endif
@elseif (Route::has('login'))
    <x-ui.button :href="route('login')" variant="ghost" size="sm">{{ __('auth::auth.login') }}</x-ui.button>
    @if (Route::has('register'))
        <x-ui.button :href="route('register')" size="sm">{{ __('auth::auth.register') }}</x-ui.button>
    @endif
@endif
