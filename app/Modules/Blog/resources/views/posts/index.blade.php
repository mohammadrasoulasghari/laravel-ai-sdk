<x-layouts.app :title="__('blog::posts.title')">
    <nav class="mb-8 flex flex-wrap gap-2" aria-label="{{ __('blog::categories.title') }}">
        <x-ui.button
            :href="route('blog.posts.index')"
            :variant="$activeCategory === null ? 'primary' : 'secondary'"
            size="sm"
        >
            {{ __('blog::posts.all_categories') }}
        </x-ui.button>
        @foreach ($categories as $category)
            <x-ui.button
                :href="route('blog.posts.index', ['filter' => ['category' => $category->slug->value]])"
                :variant="$activeCategory === $category->slug->value ? 'primary' : 'secondary'"
                size="sm"
            >
                {{ $category->name }}
            </x-ui.button>
        @endforeach
    </nav>

    @if ($posts->isEmpty())
        <x-ui.empty-state :message="__('blog::posts.empty')" />
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($posts as $post)
                <x-ui.card>
                    <article>
                        <div class="mb-2 flex items-center gap-2 text-xs text-fg-muted">
                            <x-ui.badge>{{ $post->category->name }}</x-ui.badge>
                            <x-ui.date :value="$post->published_at" />
                        </div>
                        <h2 class="text-lg font-semibold">
                            <a
                                href="{{ route('blog.posts.show', $post->slug->value) }}"
                                class="hover:text-primary"
                                >{{ $post->title }}</a
                            >
                        </h2>
                        @if ($post->excerpt)
                            <p class="mt-2 text-sm text-fg-muted">{{ $post->excerpt }}</p>
                        @endif

                        <a
                            href="{{ route('blog.posts.show', $post->slug->value) }}"
                            class="mt-4 inline-block text-sm font-medium text-primary hover:underline"
                        >
                            {{ __('blog::posts.read_more') }}
                        </a>
                    </article>
                </x-ui.card>
            @endforeach
        </div>

        <x-ui.pagination :paginator="$posts" />
    @endif
</x-layouts.app>
