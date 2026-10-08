<x-layouts.admin :title="__('admin::admin.dashboard')">
    <p class="mb-6 text-fg-muted">{{ __('admin::admin.intro') }}</p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($sections as $section)
            <a
                href="{{ route($section->route) }}"
                class="rounded-card border border-border bg-surface p-6 font-semibold hover:border-primary"
            >
                {{ __($section->label) }}
            </a>
        @endforeach
    </div>
</x-layouts.admin>
