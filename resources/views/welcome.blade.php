<x-layouts.app>
    <section class="py-16 text-center">
        <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ __('ui.welcome_title') }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-fg-muted">{{ __('ui.welcome_body') }}</p>
        @if (Route::has('register'))
            <x-ui.button :href="route('register')" class="mt-8">{{ __('ui.get_started') }}</x-ui.button>
        @endif
    </section>
</x-layouts.app>
