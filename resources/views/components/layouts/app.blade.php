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
                @if ($appNavigation !== [])
                    <details class="relative md:hidden">
                        <summary
                            class="[&::-webkit-details-marker]:hidden list-none rounded-control p-2 text-fg-muted hover:bg-surface-muted"
                        >
                            <span class="sr-only">{{ __('ui.open_menu') }}</span>
                            <x-ui.icon name="menu" class="size-5" />
                        </summary>
                        <nav
                            aria-label="{{ __('ui.main_navigation') }}"
                            class="absolute end-0 z-40 mt-2 flex w-56 flex-col gap-1 rounded-card border border-border bg-surface p-2 shadow-lg"
                        >
                            @foreach ($appNavigation as $item)
                                <x-ui.nav-link :item="$item" />
                            @endforeach
                        </nav>
                    </details>
                @endif

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
