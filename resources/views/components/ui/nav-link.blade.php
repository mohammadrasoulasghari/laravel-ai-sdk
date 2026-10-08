@props (['item'])

@php ($active = request()->routeIs($item->activePattern()))

<a
    href="{{ route($item->route) }}"
    @if ($active) aria-current="page" @endif
    {{
        $attributes->class([
            'rounded-control px-3 py-2 text-sm font-medium',
            'bg-surface-muted text-fg' => $active,
            'text-fg-muted hover:text-fg' => !$active,
        ])
    }}
>
    {{ __($item->label) }}
</a>
