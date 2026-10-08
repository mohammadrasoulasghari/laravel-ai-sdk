@props (['title' => null])

<section
    {{
        $attributes->class(
            'rounded-card border border-border bg-surface p-6 shadow-sm',
        )
    }}
>
    @if ($title)
        <h2 class="mb-4 text-lg font-semibold text-fg">{{ $title }}</h2>
    @endif

    {{ $slot }}
</section>
