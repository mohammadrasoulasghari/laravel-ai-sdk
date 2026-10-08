<x-layouts.admin :title="__('blog::posts.admin_title')">
    <x-slot:actions>
        <x-ui.button :href="route('admin.blog.posts.create')">{{ __('blog::posts.new') }}</x-ui.button>
    </x-slot:actions>

    <form method="GET" action="{{ route('admin.blog.posts.index') }}" class="mb-6 flex max-w-md gap-2" role="search">
        <x-ui.input
            name="search"
            type="search"
            :value="$search"
            :placeholder="__('blog::posts.search_placeholder')"
            :aria-label="__('ui.search')"
        />
        <x-ui.button type="submit" variant="secondary">{{ __('ui.search') }}</x-ui.button>
    </form>

    @if ($posts->isEmpty())
        <x-ui.empty-state :message="__('blog::posts.empty')" />
    @else
        <x-ui.table>
            <x-slot:head>
                <th scope="col">{{ __('blog::posts.fields.title') }}</th>
                <th scope="col">{{ __('blog::posts.fields.category') }}</th>
                <th scope="col">{{ __('blog::posts.fields.status') }}</th>
                <th scope="col">{{ __('blog::posts.fields.published_at') }}</th>
                <th scope="col"><span class="sr-only">{{ __('ui.actions') }}</span></th>
            </x-slot:head>

            @foreach ($posts as $post)
                <tr>
                    <td class="font-medium">{{ $post->title }}</td>
                    <td>{{ $post->category->name }}</td>
                    <td>
                        <x-ui.badge
                            :type="$post->status === \App\Modules\Blog\Enums\PostStatus::Published ? 'success' : 'neutral'"
                        >
                            {{ __('blog::posts.status.' . $post->status->value) }}
                        </x-ui.badge>
                    </td>
                    <td>
                        @if ($post->published_at)
                            <x-ui.date :value="$post->published_at" />
                        @endif
                    </td>
                    <td class="text-end">
                        <x-ui.button :href="route('admin.blog.posts.edit', $post)" variant="ghost" size="sm">
                            {{ __('ui.edit') }}</x-ui.button
                        >
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.pagination :paginator="$posts" />
    @endif
</x-layouts.admin>
