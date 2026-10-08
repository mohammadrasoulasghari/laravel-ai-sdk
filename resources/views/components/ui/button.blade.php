{{--
    <x-ui.button variant="primary|secondary|danger|ghost" size="sm|md" type="submit" href="...">Label</x-ui.button>
    Renders <a> when href is given, otherwise <button>.
--}}
@props ([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $base =
        'inline-flex items-center justify-center gap-2 rounded-control font-medium transition-colors disabled:pointer-events-none disabled:opacity-50';
    $variants = [
        'primary' => 'bg-primary text-primary-fg hover:bg-primary-hover',
        'secondary' => 'border border-border bg-surface text-fg hover:bg-surface-muted',
        'danger' => 'bg-danger text-danger-fg hover:bg-danger-hover',
        'ghost' => 'text-fg hover:bg-surface-muted',
    ];
    $sizes = [
        'sm' => 'h-8 px-3 text-sm',
        'md' => 'h-10 px-4 text-sm',
    ];
    $classes = implode(' ', [$base, $variants[$variant] ?? $variants['primary'], $sizes[$size] ?? $sizes['md']]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
