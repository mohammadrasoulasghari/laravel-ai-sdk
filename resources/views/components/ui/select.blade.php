{{--
    <x-ui.select name="category_id" :label="..." :options="[$id => $label, ...]" :value="$current" placeholder />
--}}
@props ([
    'name',
    'options' => [],
    'label' => null,
    'value' => null,
    'placeholder' => false,
    'id' => null,
])

@php
    $id ??= $name;
    $invalid = $errors->has($name);
    $selected = (string) old($name, $value);
@endphp

<div {{ $attributes->only('class')->class('w-full') }}>
    @if ($label)
        <x-ui.label :for="$id" :required="$attributes->has('required')">{{ $label }}</x-ui.label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($invalid) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{
            $attributes
                ->except('class')
                ->class([
                    'block h-10 w-full rounded-control border bg-surface px-3 text-sm text-fg',
                    'border-danger' => $invalid,
                    'border-border' => !$invalid,
                ])
        }}
    >
        @if ($placeholder)
            <option value="">{{ __('ui.select_placeholder') }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected ($selected === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    <x-ui.form-error :name="$name" />
</div>
