@props([
    'field',
    'items' => [],
    'current' => '',
])

@if (collect($items)->isNotEmpty())
    <div class="mt-2 flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
        @foreach ($items as $item)
            <button type="button" wire:click="$set({{ \Illuminate\Support\Js::from($field) }}, {{ \Illuminate\Support\Js::from($item) }})"
                class="shrink-0 whitespace-nowrap px-2.5 py-1 rounded-lg border text-xs font-medium transition cursor-pointer {{ $current === $item ? 'bg-sky-50 dark:bg-sky-500/15 border-sky-300 dark:border-sky-500/40 text-sky-700 dark:text-sky-300' : 'bg-zinc-50 dark:bg-zinc-800/70 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100' }}">
                {{ $item }}
            </button>
        @endforeach
    </div>
@endif
