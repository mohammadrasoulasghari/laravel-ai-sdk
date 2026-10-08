<x-layouts.app :title="__('ui.styleguide.title')">
    <p class="mb-8 text-fg-muted">{{ __('ui.styleguide.intro') }}</p>

    <div class="grid gap-8">
        <x-ui.card :title="__('ui.styleguide.buttons')">
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button>{{ __('ui.save') }}</x-ui.button>
                <x-ui.button variant="secondary">{{ __('ui.cancel') }}</x-ui.button>
                <x-ui.button variant="danger">{{ __('ui.delete') }}</x-ui.button>
                <x-ui.button variant="ghost">{{ __('ui.back') }}</x-ui.button>
                <x-ui.button size="sm">{{ __('ui.create') }}</x-ui.button>
                <x-ui.button disabled>{{ __('ui.save') }}</x-ui.button>
                <x-ui.button href="#">{{ __('ui.view') }}</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('ui.styleguide.forms')">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.input
                    name="sg_text"
                    :label="__('ui.styleguide.sample_label')"
                    :hint="__('ui.styleguide.sample_hint')"
                    required
                />
                <x-ui.input name="sg_error" :label="__('ui.styleguide.sample_label')" />
                <x-ui.select
                    name="sg_select"
                    :label="__('ui.styleguide.sample_label')"
                    :options="$options"
                    placeholder
                />
                <x-ui.textarea name="sg_textarea" :label="__('ui.styleguide.sample_label')" rows="3" />
                <x-ui.checkbox name="sg_check" :label="__('ui.styleguide.sample_label')" />
                <div>
                    <x-ui.label for="sg_date">{{ __('ui.styleguide.sample_label') }}</x-ui.label>
                    <x-ui.date id="sg_date" :value="$now" with-time />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('ui.styleguide.feedback')">
            <div class="grid gap-3">
                <x-ui.alert type="success">{{ __('ui.styleguide.sample_text') }}</x-ui.alert>
                <x-ui.alert type="error">{{ __('ui.styleguide.sample_error') }}</x-ui.alert>
                <x-ui.alert type="warning">{{ __('ui.styleguide.sample_text') }}</x-ui.alert>
                <x-ui.alert type="info">{{ __('ui.styleguide.sample_text') }}</x-ui.alert>
                <div class="flex gap-2">
                    <x-ui.badge>{{ __('ui.styleguide.sample_text') }}</x-ui.badge>
                    <x-ui.badge type="success">{{ __('ui.yes') }}</x-ui.badge>
                    <x-ui.badge type="danger">{{ __('ui.no') }}</x-ui.badge>
                    <x-ui.badge type="warning">{{ __('ui.styleguide.sample_text') }}</x-ui.badge>
                    <x-ui.badge type="info">{{ __('ui.styleguide.sample_text') }}</x-ui.badge>
                </div>
                <x-ui.empty-state />
            </div>
        </x-ui.card>

        <x-ui.card :title="__('ui.styleguide.data')">
            <x-ui.table>
                <x-slot:head>
                    <th scope="col">{{ __('ui.styleguide.column', ['number' => 1]) }}</th>
                    <th scope="col">{{ __('ui.styleguide.column', ['number' => 2]) }}</th>
                    <th scope="col">{{ __('ui.actions') }}</th>
                </x-slot:head>
                @foreach ($paginator as $row)
                    <tr>
                        <td>{{ $row }}</td>
                        <td>{{ __('ui.styleguide.sample_text') }}</td>
                        <td><x-ui.button variant="ghost" size="sm">{{ __('ui.edit') }}</x-ui.button></td>
                    </tr>
                @endforeach
            </x-ui.table>
            <x-ui.pagination :paginator="$paginator" />
        </x-ui.card>

        <x-ui.card :title="__('ui.styleguide.overlays')">
            <x-ui.modal :title="__('ui.are_you_sure')">
                <x-slot:trigger>
                    <x-ui.button variant="danger" x-on:click="show()">{{ __('ui.styleguide.open_modal') }}</x-ui.button>
                </x-slot:trigger>
                {{ __('ui.delete_confirmation') }}
                <x-slot:footer>
                    <x-ui.button variant="secondary" x-on:click="hide()">{{ __('ui.cancel') }}</x-ui.button>
                    <x-ui.button variant="danger">{{ __('ui.delete') }}</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
        </x-ui.card>
    </div>
</x-layouts.app>
