@props (['type' => 'neutral'])

@php
    $styles = [
        'neutral' => 'bg-surface-muted text-fg-muted',
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-info',
    ];
@endphp

<span
    {{
        $attributes->class([
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            $styles[$type] ?? $styles['neutral'],
        ])
    }}
>
    {{ $slot }}
</span>
