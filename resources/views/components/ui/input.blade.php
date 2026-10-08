{{--
    Text-like input with label, hint, old() value and validation state.
    <x-ui.input name="email" type="email" :label="__('validation.attributes.email')" required />
--}}
@props (['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'id' => null,])

@php
    $id ??= $name;
    $invalid = $errors->has($name);
    $describedBy = trim(($hint ? $id . '-hint ' : '') . ($invalid ? $name . '-error' : ''));
@endphp

<div {{ $attributes->only('class')->class('w-full') }}>
    @if ($label)
        <x-ui.label :for="$id" :required="$attributes->has('required')">{{ $label }}</x-ui.label>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if ($invalid) aria-invalid="true" @endif
        {{
            $attributes
                ->except('class')
                ->class([
                    'block h-10 w-full rounded-control border bg-surface px-3 text-sm text-fg placeholder:text-fg-muted',
                    'border-danger' => $invalid,
                    'border-border' => !$invalid,
                ])
        }}
    />

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-fg-muted">{{ $hint }}</p>
    @endif

    <x-ui.form-error :name="$name" />
</div>
