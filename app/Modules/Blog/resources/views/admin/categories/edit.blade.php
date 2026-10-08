<x-layouts.admin :title="__('blog::categories.edit_title')">
    <x-ui.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.blog.categories.update', $category) }}" class="grid gap-4">
            @csrf
            @method ('PUT')
            <x-ui.input
                name="name"
                :label="__('blog::categories.name')"
                :value="$category->name"
                required
                maxlength="100"
            />
            <x-ui.input name="slug" :label="__('blog::categories.slug')" :value="$category->slug->value" dir="auto" />
            <div class="flex justify-end gap-2">
                <x-ui.button :href="route('admin.blog.categories.index')" variant="secondary">
                    {{ __('ui.cancel') }}</x-ui.button
                >
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.admin>
