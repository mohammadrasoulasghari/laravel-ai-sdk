{{-- Validation message for one field. <x-ui.form-error name="email" /> --}}
@props (['name', 'bag' => 'default'])

@error ($name, $bag)
    <p id="{{ $name }}-error" {{ $attributes->class('mt-1.5 text-sm text-danger') }} role="alert">{{ $message }}</p>
@enderror
