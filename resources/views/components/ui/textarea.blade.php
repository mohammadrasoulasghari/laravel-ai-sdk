@props (['name', 'label' => null, 'value' => null, 'hint' => null, 'id' => null, 'rows' => 5,])

@php
    $id ??= $name;
    $invalid = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->class('w-full') }}>
    @if ($label)
        <x-ui.label :for="$id" :required="$attributes->has('required')">{{ $label }}</x-ui.label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($invalid) aria-invalid="true" @endif
        @if ($hint || $invalid) aria-describedby="{{ trim(($hint ? $id.'-hint ' : '').($invalid ? $name.'-error' : '')) }}" @endif
        {{
            $attributes
                ->except('class')
                ->class([
                    'block w-full rounded-control border bg-surface px-3 py-2 text-sm text-fg',
                    'border-danger' => $invalid,
                    'border-border' => !$invalid,
                ])
        }}
        >{{ old($name, $value) }}</textarea
    >

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-fg-muted">{{ $hint }}</p>
    @endif

    <x-ui.form-error :name="$name" />
</div>
