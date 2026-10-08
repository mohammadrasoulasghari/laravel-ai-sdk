{{-- Main application layout. <x-layouts.app :title="__('...')">…</x-layouts.app> --}}
@props (['title' => null])

<x-layouts.base :title="$title">
    <header class="border-b border-border bg-surface">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-base font-bold">{{ config('app.name') }}</a>

                <nav aria-label="{{ __('ui.main_navigation') }}" class="hidden items-center gap-1 md:flex">
                    @foreach ($appNavigation as $item)
                        <x-ui.nav-link :item="$item" />
                    @endforeach
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.locale-switcher :locales="$locales" :current="$currentLocale" />
                <x-ui.theme-toggle />

                @isset ($actions)
                    {{ $actions }}
                @endisset

                <x-layouts.partials.user-menu :user="$currentUser" />
            </div>
        </div>
    </header>

    <main id="main" class="mx-auto max-w-6xl px-4 py-8">
        <x-ui.flash />

        @if ($title)
            <h1 class="mb-6 text-2xl font-bold">{{ $title }}</h1>
        @endif

        {{ $slot }}
    </main>
</x-layouts.base>
