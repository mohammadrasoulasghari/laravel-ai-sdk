{{--
    <x-ui.modal :title="__('ui.are_you_sure')">
        <x-slot:trigger><x-ui.button variant="danger" x-on:click="show()">…</x-ui.button></x-slot:trigger>
        Body…
        <x-slot:footer>…</x-slot:footer>
    </x-ui.modal>
--}}
@props (['title', 'trigger' => null, 'footer' => null, 'open' => false])

@php ($titleId = 'modal-' . \Illuminate\Support\Str::random(8))

<div x-data="modal(@js($open))" x-on:keydown.escape.window="open && hide()" {{ $attributes }}>
    {{ $trigger }}

    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" x-on:click="hide()" aria-hidden="true"></div>

            <div
                x-ref="panel"
                x-show="open"
                x-transition
                role="dialog"
                aria-modal="true"
                aria-labelledby="{{ $titleId }}"
                class="relative w-full max-w-lg rounded-card bg-surface p-6 shadow-xl"
            >
                <div class="mb-4 flex items-start justify-between gap-4">
                    <h2 id="{{ $titleId }}" class="text-lg font-semibold">{{ $title }}</h2>
                    <button
                        type="button"
                        x-on:click="hide()"
                        class="rounded-control p-1 text-fg-muted hover:bg-surface-muted"
                    >
                        <span class="sr-only">{{ __('ui.close') }}</span>
                        <x-ui.icon name="x" class="size-5" />
                    </button>
                </div>

                <div class="text-sm text-fg-muted">{{ $slot }}</div>

                @if ($footer)
                    <div class="mt-6 flex justify-end gap-2">{{ $footer }}</div>
                @endif
            </div>
        </div>
    </template>
</div>
