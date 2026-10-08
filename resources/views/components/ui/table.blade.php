{{--
    <x-ui.table>
        <x-slot:head><th scope="col">…</th></x-slot:head>
        <tr>…</tr>
    </x-ui.table>
--}}
@props (['head'])

<div
    {{
        $attributes->class(
            'overflow-x-auto rounded-card border border-border bg-surface',
        )
    }}
>
    <table class="w-full text-start text-sm">
        <thead
            class="[&_th]:px-4 [&_th]:py-3 [&_th]:text-start border-b border-border bg-surface-muted text-xs font-semibold text-fg-muted uppercase"
        >
            <tr>
                {{ $head }}
            </tr>
        </thead>
        <tbody class="[&_td]:px-4 [&_td]:py-3 divide-y divide-border">
            {{ $slot }}
        </tbody>
    </table>
</div>
