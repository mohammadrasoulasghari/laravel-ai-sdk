{{-- Shared create/edit form. $post is null on create. --}}
<x-layouts.admin :title="$post ? __('blog::posts.edit_title') : __('blog::posts.create_title')">
    @if ($post)
        <x-slot:actions>
            <div class="flex gap-2">
                @if ($post->status === \App\Modules\Blog\Enums\PostStatus::Published)
                    <x-ui.button :href="route('blog.posts.show', $post->slug->value)" variant="secondary">
                        {{ __('blog::posts.view_public') }}</x-ui.button
                    >
                    <form method="POST" action="{{ route('admin.blog.posts.unpublish', $post) }}">
                        @csrf
                        @method ('DELETE')
                        <x-ui.button type="submit" variant="secondary">{{ __('blog::posts.unpublish') }}</x-ui.button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.blog.posts.publish', $post) }}">
                        @csrf
                        <x-ui.button type="submit">{{ __('blog::posts.publish') }}</x-ui.button>
                    </form>
                @endif
            </div>
        </x-slot:actions>
    @endif

    <x-ui.card class="max-w-3xl">
        <form
            method="POST"
            action="{{ $post ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store') }}"
            class="grid gap-5"
        >
            @csrf
            @if ($post)
                @method ('PUT')
            @endif

            <x-ui.input
                name="title"
                :label="__('blog::posts.fields.title')"
                :value="$post?->title"
                required
                maxlength="200"
            />
            <x-ui.input
                name="slug"
                :label="__('blog::posts.fields.slug')"
                :hint="__('blog::posts.fields.slug_hint')"
                :value="$post?->slug->value"
                dir="auto"
            />
            <x-ui.select
                name="category_id"
                :label="__('blog::posts.fields.category')"
                :options="$categoryOptions"
                :value="$post?->category_id"
                placeholder
                required
            />
            <x-ui.textarea
                name="excerpt"
                :label="__('blog::posts.fields.excerpt')"
                :value="$post?->excerpt"
                rows="2"
                maxlength="500"
            />
            <x-ui.textarea
                name="body"
                :label="__('blog::posts.fields.body')"
                :hint="__('blog::posts.fields.body_hint')"
                :value="$post?->body"
                rows="14"
                required
            />

            <div class="flex justify-end gap-2">
                <x-ui.button :href="route('admin.blog.posts.index')" variant="secondary">
                    {{ __('ui.cancel') }}</x-ui.button
                >
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($post)
        <div class="mt-6 max-w-3xl">
            <x-ui.modal :title="__('ui.are_you_sure')">
                <x-slot:trigger>
                    <x-ui.button variant="danger" x-on:click="show()">{{ __('ui.delete') }}</x-ui.button>
                </x-slot:trigger>
                {{
                    __('blog::posts.delete_confirm', [
                        'title' => $post->title,
                    ])
                }}
                <x-slot:footer>
                    <x-ui.button variant="secondary" x-on:click="hide()">{{ __('ui.cancel') }}</x-ui.button>
                    <form method="POST" action="{{ route('admin.blog.posts.destroy', $post) }}">
                        @csrf
                        @method ('DELETE')
                        <x-ui.button type="submit" variant="danger">{{ __('ui.delete') }}</x-ui.button>
                    </form>
                </x-slot:footer>
            </x-ui.modal>
        </div>
    @endif
</x-layouts.admin>
