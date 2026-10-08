<button
    type="button"
    x-data
    x-on:click="$store.theme.toggle()"
    {{
        $attributes->class(
            'rounded-control p-2 text-fg-muted hover:bg-surface-muted hover:text-fg',
        )
    }}
>
    <span class="sr-only">{{ __('ui.toggle_theme') }}</span>
    <x-ui.icon name="moon" class="size-5 dark:hidden" />
    <x-ui.icon name="sun" class="hidden size-5 dark:block" />
</button>
