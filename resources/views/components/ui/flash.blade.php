{{-- Renders the standard flash payload set by App\Shared\Http\Flash. Included by layouts. --}}
@if (session()->has(\App\Shared\Http\Flash::SESSION_KEY))
    @php ($flash = session(\App\Shared\Http\Flash::SESSION_KEY))
    <x-ui.alert :type="$flash['type']" {{ $attributes->class('mb-6') }}>{{ $flash['message'] }}</x-ui.alert>
@endif
