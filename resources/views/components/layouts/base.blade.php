{{-- Document shell shared by every layout. Locale, direction and theme are set here only. --}}
@props (['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ? $title . ' · ' . config('app.name') : config('app.name') }}</title>
    {{-- Apply the theme before first paint to avoid a light/dark flash. --}}
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (() => {
            const mode = localStorage.getItem('theme') ?? 'system';
            const dark = mode === 'dark' || (mode === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    @vite (['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body
    {{
        $attributes->class(
            'min-h-screen bg-bg font-sans text-fg',
        )
    }}
>
    <a
        href="#main"
        class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-surface focus:px-4 focus:py-2"
    >
        {{ __('ui.skip_to_content') }}
    </a>

    {{ $slot }}
</body>
</html>
