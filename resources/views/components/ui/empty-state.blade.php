@props (['message' => null])

<div
    {{
        $attributes->class(
            'rounded-card border border-dashed border-border p-10 text-center text-sm text-fg-muted',
        )
    }}
>
    {{ $message ?? __('ui.no_results') }} {{ $slot }}
</div>
