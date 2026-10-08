@props (['locales', 'current'])

<div {{ $attributes->class('flex items-center gap-1') }} role="group" aria-label="{{ __('ui.language') }}">
    @foreach ($locales as $code => $name)
        <form method="POST" action="{{ route('locale.switch', $code) }}">
            @csrf
            <button
                type="submit"
                lang="{{ $code }}"
                @if ($code === $current) aria-current="true" @endif
                @class ([
                    'rounded-control px-2 py-1 text-xs font-medium',
                    'bg-surface-muted text-fg' => $code === $current,
                    'text-fg-muted hover:text-fg' => $code !== $current
                ])
            >
                {{ $name }}
            </button>
        </form>
    @endforeach
</div>
