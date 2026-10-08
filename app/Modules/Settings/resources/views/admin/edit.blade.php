<x-layouts.admin :title="__('settings::settings.title')">
    <x-ui.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-5">
            @csrf
            @method ('PUT')

            @foreach ($keys as $key)
                @if ($key->isBoolean())
                    <div>
                        <x-ui.checkbox
                            :name="$key->value"
                            :label="__('settings::settings.keys.' . $key->value . '.label')"
                            :checked="$values[$key->value]"
                        />
                        <p class="mt-1 ps-6 text-sm text-fg-muted">{{ __('settings::settings.keys.' . $key->value . '.hint') }}</p>
                    </div>
                @else
                    <x-ui.input
                        :name="$key->value"
                        :label="__('settings::settings.keys.' . $key->value . '.label')"
                        :hint="__('settings::settings.keys.' . $key->value . '.hint')"
                        :value="$values[$key->value]"
                    />
                @endif
            @endforeach

            <div class="flex justify-end">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.admin>
