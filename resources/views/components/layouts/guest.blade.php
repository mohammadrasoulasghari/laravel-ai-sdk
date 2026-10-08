{{-- Centered card layout for auth screens. --}}
@props (['title' => null])

<x-layouts.base :title="$title">
    <div class="flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-12">
        <div class="flex w-full max-w-md items-center justify-between">
            <a href="{{ route('home') }}" class="text-lg font-bold">{{ config('app.name') }}</a>
            <div class="flex items-center gap-2">
                <x-ui.locale-switcher :locales="$locales" :current="$currentLocale" />
                <x-ui.theme-toggle />
            </div>
        </div>

        <main id="main" class="w-full max-w-md">
            <x-ui.card>
                @if ($title)
                    <h1 class="mb-6 text-xl font-bold">{{ $title }}</h1>
                @endif

                <x-ui.flash />

                {{ $slot }}
            </x-ui.card>
        </main>
    </div>
</x-layouts.base>
