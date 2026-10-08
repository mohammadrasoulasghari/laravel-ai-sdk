<x-layouts.app>
    <article class="mx-auto max-w-3xl">
        <a
            href="{{ route('blog.posts.index') }}"
            class="text-sm text-fg-muted hover:text-fg"
            >{{ __('blog::posts.back_to_blog') }}</a
        >

        <header class="mt-4 mb-8">
            <h1 class="text-3xl font-black tracking-tight sm:text-4xl">{{ $post->title }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-fg-muted">
                <span>{{
                    __('blog::posts.by_author', [
                        'name' => $post->author->name,
                    ])
                }}</span>
                <x-ui.date :value="$post->published_at" />
                <x-ui.badge>{{ $post->category->name }}</x-ui.badge>
            </div>
        </header>

        <div
            class="prose-content [&_a]:text-primary [&_a]:underline [&_h2]:text-xl [&_h2]:font-bold [&_ol]:list-decimal [&_ol]:ps-6 [&_ul]:list-disc [&_ul]:ps-6 space-y-4 leading-8"
        >
            {{-- unescaped: body_html is CommonMark output with raw HTML stripped and unsafe links disabled (Post::bodyHtml). --}}
            {!! $post->body_html !!}
        </div>
    </article>
</x-layouts.app>
