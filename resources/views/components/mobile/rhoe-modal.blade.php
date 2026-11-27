@props([
    'model' => 'open',
    'title' => '',
    'subtitle' => null,
    'maxWidth' => 'max-w-[400px]',
])

<div x-show="{{ $model }}"
    x-cloak
    @keydown.escape.window="{{ $model }} = false"
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 pb-24"
    role="dialog"
    aria-modal="true">

    <!-- Backdrop -->
    <div x-show="{{ $model }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="{{ $model }} = false"
        class="fixed inset-0 bg-black/55 backdrop-blur-xs"></div>

    <!-- Modal Surface (Rhoe UI Spring Bounce + Fade) -->
    <div x-show="{{ $model }}"
        x-transition:enter="transition ease-[cubic-bezier(0.34,1.56,0.64,1)] duration-300"
        x-transition:enter-start="opacity-0 scale-75 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-90 translate-y-2"
        class="relative z-10 w-full {{ $maxWidth }} max-h-[82vh] flex flex-col rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200/90 dark:border-zinc-800 shadow-2xl shadow-black/25 overflow-hidden">

        <!-- Header -->
        <div class="px-5 pt-5 pb-3.5 border-b border-zinc-100 dark:border-zinc-800/80 flex items-start justify-between gap-3 shrink-0">
            <div class="min-w-0">
                <h3 class="text-sm font-extrabold tracking-tight text-zinc-900 dark:text-white truncate">
                    {{ $title }}
                </h3>
                @if ($subtitle)
                    <p class="text-[13px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
            <button type="button" @click="{{ $model }} = false" aria-label="Tutup"
                class="w-7 h-7 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-500 dark:text-zinc-300 flex items-center justify-center text-xs transition-colors cursor-pointer shrink-0">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="px-5 py-4 overflow-y-auto flex-1 text-xs text-zinc-700 dark:text-zinc-300">
            {{ $slot }}
        </div>

        <!-- Optional Footer -->
        @isset($footer)
            <div class="px-5 py-3.5 bg-zinc-50/80 dark:bg-zinc-950/60 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-end gap-2 shrink-0">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
