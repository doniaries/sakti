@props([
    'title',
    'subtitle' => null,
    'backUrl' => route('mobile.dashboard'),
])

<div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200/80 dark:border-zinc-800 px-4 py-3 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
        <a href="{{ $backUrl }}" wire:navigate.hover
            class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200 active:scale-95 transition-transform shrink-0">
            <span class="material-symbols-rounded text-[20px]">arrow_back</span>
        </a>
        <div class="min-w-0">
            <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-[13px] text-zinc-500 dark:text-zinc-400 truncate">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if (isset($actions))
        <div class="shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
