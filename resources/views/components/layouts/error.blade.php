{{-- Minimal layout for HTTP error pages (works without session or auth). --}}
@props (['code', 'title', 'message'])

<x-layouts.base :title="$title">
    <main id="main" class="flex min-h-screen flex-col items-center justify-center gap-4 px-4 text-center">
        <p class="text-6xl font-black text-primary">{{ $code }}</p>
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <p class="max-w-md text-fg-muted">{{ $message }}</p>
        <x-ui.button :href="url('/')" variant="secondary">{{ __('ui.home') }}</x-ui.button>
    </main>
</x-layouts.base>
