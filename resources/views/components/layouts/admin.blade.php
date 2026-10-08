{{-- Admin panel layout with sidebar navigation fed by NavigationArea::Admin items. --}}
@props (['title' => null])

<x-layouts.base :title="$title">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-e border-border bg-surface md:block">
            <div class="flex h-16 items-center px-6 font-bold">{{ __('ui.admin_panel') }}</div>
            <nav aria-label="{{ __('ui.admin_navigation') }}" class="flex flex-col gap-1 px-3">
                @foreach ($adminNavigation as $item)
                    <x-ui.nav-link :item="$item" />
                @endforeach
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between gap-4 border-b border-border bg-surface px-6">
                <a
                    href="{{ route('home') }}"
                    class="text-sm text-fg-muted hover:text-fg"
                    >{{ __('ui.back_to_site') }}</a
                >
                <div class="flex items-center gap-2">
                    <x-ui.locale-switcher :locales="$locales" :current="$currentLocale" />
                    <x-ui.theme-toggle />
                    <x-layouts.partials.user-menu :user="$currentUser" />
                </div>
            </header>

            <main id="main" class="flex-1 px-6 py-8">
                <x-ui.flash />

                @if ($title)
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <h1 class="text-2xl font-bold">{{ $title }}</h1>
                        {{ $actions ?? '' }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
