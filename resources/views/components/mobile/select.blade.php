@props([
    'wireModel',
    'value' => '',
    'placeholder' => 'Pilih...',
    'emptyLabel' => null,
    'groups' => [],
    'searchPlaceholder' => 'Cari...',
    'searchable' => true,
    'inline' => false,
])

@php
    $currentVal = (string) ($value ?? '');
    $selectedLabel = $emptyLabel ?? $placeholder;
    foreach ($groups as $group) {
        foreach ($group['options'] ?? [] as $opt) {
            if ((string) ($opt['value'] ?? '') === $currentVal && $currentVal !== '') {
                $selectedLabel = $opt['label'];
                break 2;
            }
        }
    }
@endphp

<div x-data="{
        open: false,
        search: '',
        activeGroup: 'all',
        model: @js($wireModel),
        current: @js($currentVal),
        selectOption(val) {
            this.current = String(val);
            this.open = false;
            this.search = '';
            this.activeGroup = 'all';
            $wire.set(this.model, val === '' ? null : val);
        },
        matches(text, group = null) {
            if (group !== null && this.activeGroup !== 'all' && this.activeGroup !== group) return false;
            if (!this.search.trim()) return true;
            return text.toLowerCase().includes(this.search.trim().toLowerCase());
        }
    }"
    @keydown.escape.window="open = false"
    class="relative w-full">
    <button type="button"
        @click="open = !open; @if ($searchable) if (open) { $nextTick(() => $refs.searchInput?.focus({ preventScroll: true })); } @endif"
        class="w-full flex items-center justify-between gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#132338] px-3.5 py-3 text-left text-sm text-slate-800 dark:text-slate-100 shadow-2xs hover:border-sky-400 dark:hover:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/30 transition cursor-pointer">
        <span class="leading-snug line-clamp-2 {{ $currentVal === '' && ! $emptyLabel ? 'text-slate-400 dark:text-slate-500' : 'font-medium' }}">
            {{ $selectedLabel }}
        </span>
        <i class="fa-solid fa-chevron-down text-xs text-slate-400 shrink-0 transition-transform duration-150"
            :class="open ? 'rotate-180 text-sky-500' : ''"></i>
    </button>

    <div x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        @click.outside="open = false"
        class="{{ $inline ? 'relative mt-1.5' : 'absolute left-0 right-0 z-[60] mt-1.5' }} rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0F1E33] shadow-xl overflow-hidden">
        @if ($searchable || count($groups) > 1)
            <div class="p-2 border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-[#132338] space-y-1.5">
                @if ($searchable)
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input x-ref="searchInput"
                            x-model="search"
                            type="text"
                            placeholder="{{ $searchPlaceholder }}"
                            class="w-full pl-8 pr-3 py-2 rounded-lg bg-white dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    </div>
                @endif
                @if (count($groups) > 1)
                    @php
                        $totalGroupOptions = collect($groups)->sum(fn ($g) => count($g['options'] ?? []));
                    @endphp
                    <div class="flex items-center gap-1 overflow-x-auto no-scrollbar pb-0.5">
                        <button type="button"
                            @click="activeGroup = 'all'"
                            :class="activeGroup === 'all' ? 'bg-sky-600 text-white' : 'bg-white dark:bg-[#0F1E33] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                            class="shrink-0 px-2 py-0.5 rounded-full text-xs font-bold transition cursor-pointer">
                            Semua ({{ $totalGroupOptions }})
                        </button>
                        @foreach ($groups as $g)
                            @if (! empty($g['label']))
                                <button type="button"
                                    @click="activeGroup = @js($g['label'])"
                                    :class="activeGroup === @js($g['label']) ? 'bg-sky-600 text-white' : 'bg-white dark:bg-[#0F1E33] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                    class="shrink-0 max-w-[180px] truncate px-2 py-0.5 rounded-full text-xs font-bold transition cursor-pointer"
                                    title="{{ $g['label'] }}">
                                    {{ \Illuminate\Support\Str::limit($g['label'], 20) }} ({{ count($g['options'] ?? []) }})
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="max-h-52 overflow-y-auto overscroll-contain divide-y divide-slate-100/70 dark:divide-slate-800/70">
            @if ($emptyLabel)
                <button type="button"
                    x-show="matches(@js($emptyLabel))"
                    @click="selectOption('')"
                    class="w-full px-3.5 py-3 text-left text-sm flex items-center justify-between gap-2 hover:bg-sky-50 dark:hover:bg-sky-500/10 transition cursor-pointer {{ $currentVal === '' ? 'bg-sky-50/80 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 font-bold' : 'text-slate-700 dark:text-slate-200 font-medium' }}">
                    <span>{{ $emptyLabel }}</span>
                    @if ($currentVal === '')
                        <i class="fa-solid fa-check text-xs text-sky-600 dark:text-sky-400 shrink-0"></i>
                    @endif
                </button>
            @endif

            @foreach ($groups as $group)
                @php
                    $groupLabel = (string) ($group['label'] ?? '');
                    $groupOptions = $group['options'] ?? [];
                    $groupText = collect($groupOptions)->pluck('label')->implode(' ');
                @endphp
                @if (count($groupOptions) > 0)
                    <div x-show="matches(@js($groupLabel . ' ' . $groupText), @js($groupLabel))">
                        @if ($groupLabel !== '')
                            <div class="px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-sky-700 dark:text-sky-300 bg-sky-50/80 dark:bg-sky-500/10 border-y border-sky-100/80 dark:border-sky-500/20 flex items-center justify-between gap-2">
                                <span class="truncate">{{ $groupLabel }}</span>
                                <span class="shrink-0 px-1.5 py-0.5 rounded-full bg-sky-100 dark:bg-sky-500/20 text-[11px]">{{ count($groupOptions) }}</span>
                            </div>
                        @endif
                        @foreach ($groupOptions as $opt)
                            @php
                                $optVal = (string) ($opt['value'] ?? '');
                                $optLabel = (string) ($opt['label'] ?? '');
                                $optBadge = $opt['badge'] ?? null;
                                $isSelected = $optVal === $currentVal && $currentVal !== '';
                            @endphp
                            <button type="button"
                                x-show="matches(@js($groupLabel . ' ' . $optLabel))"
                                @click="selectOption(@js($opt['value']))"
                                class="w-full px-3.5 py-3 text-left text-sm flex items-start justify-between gap-2 hover:bg-sky-50 dark:hover:bg-sky-500/10 transition cursor-pointer {{ $isSelected ? 'bg-sky-50/80 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 font-semibold' : 'text-slate-700 dark:text-slate-200' }}">
                                <span class="leading-snug">{{ $optLabel }}</span>
                                <span class="flex items-center gap-1.5 shrink-0 mt-0.5">
                                    @if ($optBadge)
                                        @php
                                            $isZeroBadge = str_starts_with((string) $optBadge, '0 ');
                                        @endphp
                                        <span class="px-1.5 py-0.5 rounded-md text-xs font-bold {{ $isZeroBadge ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' : 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' }}">
                                            {{ $optBadge }}
                                        </span>
                                    @endif
                                    @if ($isSelected)
                                        <i class="fa-solid fa-check text-xs text-sky-600 dark:text-sky-400"></i>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
