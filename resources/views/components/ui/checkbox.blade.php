@props (['name', 'label', 'checked' => false, 'value' => '1', 'id' => null])

@php ($id ??= $name)

<div
    {{
        $attributes
            ->only('class')
            ->class('flex items-center gap-2')
    }}
>
    <input type="hidden" name="{{ $name }}" value="0" />
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="checkbox"
        value="{{ $value }}"
        @checked (old($name, $checked))
        {{
            $attributes
                ->except('class')
                ->class('size-4 rounded border-border text-primary')
        }}
    />
    <label for="{{ $id }}" class="text-sm text-fg">{{ $label }}</label>
</div>
