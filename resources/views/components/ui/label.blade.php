@props (['for', 'required' => false])

<label
    for="{{ $for }}"
    {{
        $attributes->class(
            'mb-1.5 block text-sm font-medium text-fg',
        )
    }}
>
    {{ $slot }}
    @if ($required)
        <span class="text-danger" aria-hidden="true">*</span>
    @endif
</label>
