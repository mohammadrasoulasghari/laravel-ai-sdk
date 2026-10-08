<x-layouts.admin :title="__('blog::categories.title')">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($categories->isEmpty())
                <x-ui.empty-state />
            @else
                <x-ui.table>
                    <x-slot:head>
                        <th scope="col">{{ __('blog::categories.name') }}</th>
                        <th scope="col">{{ __('blog::categories.slug') }}</th>
                        <th scope="col">{{ __('blog::categories.posts') }}</th>
                        <th scope="col"><span class="sr-only">{{ __('ui.actions') }}</span></th>
                    </x-slot:head>

                    @foreach ($categories as $category)
                        <tr>
                            <td class="font-medium">{{ $category->name }}</td>
                            <td dir="auto" class="font-mono text-xs">{{ $category->slug }}</td>
                            <td>{{ $category->posts_count }}</td>
                            <td class="text-end">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button
                                        :href="route('admin.blog.categories.edit', $category)"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        {{ __('ui.edit') }}</x-ui.button
                                    >
                                    <form
                                        method="POST"
                                        action="{{ route('admin.blog.categories.destroy', $category) }}"
                                    >
                                        @csrf
                                        @method ('DELETE')
                                        <x-ui.button
                                            type="submit"
                                            variant="ghost"
                                            size="sm"
                                            class="text-danger"
                                            x-data
                                            x-on:click="if (! confirm(@js(__('blog::categories.delete_confirm', ['name' => $category->name])))) $event.preventDefault()"
                                        >
                                            {{ __('ui.delete') }}
                                        </x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </div>

        <x-ui.card :title="__('blog::categories.new')">
            <form method="POST" action="{{ route('admin.blog.categories.store') }}" class="grid gap-4">
                @csrf
                <x-ui.input name="name" :label="__('blog::categories.name')" required maxlength="100" />
                <x-ui.input
                    name="slug"
                    :label="__('blog::categories.slug')"
                    :hint="__('blog::posts.fields.slug_hint')"
                    dir="auto"
                />
                <x-ui.button type="submit">{{ __('ui.create') }}</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.admin>
