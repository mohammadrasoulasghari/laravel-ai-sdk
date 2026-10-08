{{-- <x-ui.alert type="success|error|warning|info">Message</x-ui.alert> --}}
@props (['type' => 'info'])

@php
    $styles = [
        'success' => 'border-success/30 bg-success-soft text-success',
        'error' => 'border-danger/30 bg-danger-soft text-danger',
        'warning' => 'border-warning/30 bg-warning-soft text-warning',
        'info' => 'border-info/30 bg-info-soft text-info',
    ];
@endphp

<div
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
    {{
        $attributes->class([
            'rounded-control border px-4 py-3 text-sm',
            $styles[$type] ?? $styles['info'],
        ])
    }}
>
    {{ $slot }}
</div>
