@props([
    'wireModel' => null,
    'value' => '',
    'title' => null,
    'placeholder' => 'Pilih...',
    'emptyLabel' => null,
    'groups' => [],
    'options' => [],
    'searchPlaceholder' => 'Cari...',
    'searchable' => true,
    'inline' => false,
    'buttonClass' => null,
])

@php
    $normalizedGroups = $groups;
    if (empty($normalizedGroups) && ! empty($options)) {
        $flatOptions = [];
        foreach ($options as $k => $v) {
            if (is_array($v) && array_key_exists('value', $v)) {
                $flatOptions[] = $v;
            } else {
                $flatOptions[] = ['value' => (string) $k, 'label' => (string) $v];
            }
        }
        $normalizedGroups = [['label' => '', 'options' => $flatOptions]];
    }

    $currentVal = (string) ($value ?? '');
    $selectedLabel = $emptyLabel ?? $placeholder;
    $labelMap = [];
    foreach ($normalizedGroups as $group) {
        foreach ($group['options'] ?? [] as $opt) {
            $optVal = (string) ($opt['value'] ?? '');
            $optLabel = (string) ($opt['label'] ?? '');
            if ($optVal !== '') {
                $labelMap[$optVal] = $optLabel;
            }
            if ($optVal === $currentVal && $currentVal !== '') {
                $selectedLabel = $optLabel;
            }
        }
    }

    $triggerClass = $buttonClass ?? 'w-full flex items-center justify-between gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#132338] px-3.5 py-2.5 text-left text-sm text-slate-800 dark:text-slate-100 shadow-2xs hover:border-sky-400 dark:hover:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/30 transition cursor-pointer';
@endphp

<div x-data="{
        open: false,
        search: '',
        activeGroup: 'all',
        model: @js($wireModel),
        current: @js($currentVal),
        labelMap: @js($labelMap),
        emptyText: @js($emptyLabel ?? $placeholder),
        get displayLabel() {
            const key = String(this.current ?? '');
            if (key !== '' && this.labelMap[key]) {
                return this.labelMap[key];
            }
            return this.emptyText;
        },
        selectOption(val) {
            const nextVal = val === null || val === undefined ? '' : String(val);
            this.current = nextVal;
            this.open = false;
            this.search = '';
            this.activeGroup = 'all';
            if (this.model) {
                $wire.set(this.model, nextVal === '' ? null : val);
            }
        },
        matches(text, group = null) {
            if (group !== null && this.activeGroup !== 'all' && this.activeGroup !== group) return false;
            if (!this.search.trim()) return true;
            return text.toLowerCase().includes(this.search.trim().toLowerCase());
        }
    }"
    @if (! $wireModel)
        x-modelable="current"
        {{ $attributes->whereStartsWith('wire:model') }}
    @endif
    @keydown.escape.window="open = false"
    class="relative w-full">
    <button type="button"
        @click="open = true; @if ($searchable) $nextTick(() => $refs.searchInput?.focus({ preventScroll: true })); @endif"
        class="{{ $triggerClass }} flex items-center justify-between gap-2 text-left cursor-pointer">
        <span class="leading-snug truncate"
            :class="(current === '' || current === null) && !@js((bool) $emptyLabel) ? 'text-slate-400 dark:text-slate-500' : 'font-medium'"
            x-text="displayLabel"></span>
        <span class="material-symbols-rounded text-[18px] text-slate-400 shrink-0 transition-transform duration-150"
            :class="open ? 'rotate-180 text-sky-500' : ''">expand_more</span>
    </button>

    <template x-teleport="body">
        <div x-show="open"
            x-cloak
            style="z-index: 1100;"
            class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4"
            role="dialog"
            aria-modal="true">
            <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="open = false"
                class="fixed inset-0 bg-slate-950/55 backdrop-blur-xs"></div>

            <div x-show="open"
                x-transition:enter="transition ease-[cubic-bezier(0.22,1,0.36,1)] duration-250"
                x-transition:enter-start="translate-y-8 sm:scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave-end="translate-y-6 sm:scale-95 opacity-0"
                class="relative z-10 w-full max-w-md max-h-[82dvh] flex flex-col rounded-t-3xl sm:rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-2xl overflow-hidden">
                <div class="pt-2.5 pb-1 flex justify-center sm:hidden shrink-0">
                    <span class="w-10 h-1 rounded-full bg-slate-200 dark:bg-zinc-700"></span>
                </div>

                <div class="px-4 py-3 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between gap-2 shrink-0">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate">
                        {{ $title ?? $placeholder }}
                    </h3>
                    <button type="button" @click="open = false"
                        class="w-8 h-8 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-500 flex items-center justify-center cursor-pointer">
                        <span class="material-symbols-rounded text-[18px]">close</span>
                    </button>
                </div>

                @if ($searchable || count($normalizedGroups) > 1)
                    <div class="p-3 border-b border-slate-100 dark:border-zinc-800 bg-slate-50/70 dark:bg-zinc-950/50 space-y-2 shrink-0">
                        @if ($searchable)
                            <div class="relative">
                                <span class="material-symbols-rounded text-[18px] text-slate-400 absolute left-3 top-1/2 -translate-y-1/2">search</span>
                                <input x-ref="searchInput"
                                    x-model="search"
                                    type="search"
                                    placeholder="{{ $searchPlaceholder }}"
                                    class="w-full h-10 pl-9 pr-3 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                            </div>
                        @endif
                        @if (count($normalizedGroups) > 1)
                            @php
                                $totalGroupOptions = collect($normalizedGroups)->sum(fn ($g) => count($g['options'] ?? []));
                            @endphp
                            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
                                <button type="button"
                                    @click="activeGroup = 'all'"
                                    :class="activeGroup === 'all' ? 'bg-sky-600 text-white' : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-zinc-700'"
                                    class="shrink-0 px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer">
                                    Semua ({{ $totalGroupOptions }})
                                </button>
                                @foreach ($normalizedGroups as $g)
                                    @if (! empty($g['label']))
                                        <button type="button"
                                            @click="activeGroup = @js($g['label'])"
                                            :class="activeGroup === @js($g['label']) ? 'bg-sky-600 text-white' : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-zinc-700'"
                                            class="shrink-0 max-w-[180px] truncate px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer"
                                            title="{{ $g['label'] }}">
                                            {{ \Illuminate\Support\Str::limit($g['label'], 20) }} ({{ count($g['options'] ?? []) }})
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div class="overflow-y-auto overscroll-contain divide-y divide-slate-100/80 dark:divide-zinc-800/80 pb-4">
                    @if ($emptyLabel)
                        <button type="button"
                            x-show="matches(@js($emptyLabel))"
                            @click="selectOption('')"
                            class="w-full px-4 py-3 text-left text-sm flex items-center justify-between gap-2 hover:bg-sky-50 dark:hover:bg-sky-500/10 transition cursor-pointer"
                            :class="(current === '' || current === null) ? 'bg-sky-50/80 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 font-bold' : 'text-slate-700 dark:text-slate-200 font-medium'">
                            <span>{{ $emptyLabel }}</span>
                            <span class="material-symbols-rounded text-[18px] shrink-0"
                                :class="(current === '' || current === null) ? 'text-sky-600 dark:text-sky-400' : 'text-slate-300 dark:text-zinc-600'"
                                x-text="(current === '' || current === null) ? 'radio_button_checked' : 'radio_button_unchecked'"></span>
                        </button>
                    @endif

                    @foreach ($normalizedGroups as $group)
                        @php
                            $groupLabel = (string) ($group['label'] ?? '');
                            $groupOptions = $group['options'] ?? [];
                            $groupText = collect($groupOptions)->pluck('label')->implode(' ');
                        @endphp
                        @if (count($groupOptions) > 0)
                            <div x-show="matches(@js($groupLabel . ' ' . $groupText), @js($groupLabel))">
                                @if ($groupLabel !== '')
                                    <div class="px-4 py-1.5 text-xs font-extrabold uppercase tracking-wider text-sky-700 dark:text-sky-300 bg-sky-50/80 dark:bg-sky-500/10 border-y border-sky-100/80 dark:border-sky-500/20 flex items-center justify-between gap-2">
                                        <span class="truncate">{{ $groupLabel }}</span>
                                        <span class="shrink-0 px-1.5 py-0.5 rounded-full bg-sky-100 dark:bg-sky-500/20 text-[11px]">{{ count($groupOptions) }}</span>
                                    </div>
                                @endif
                                @foreach ($groupOptions as $opt)
                                    @php
                                        $optVal = (string) ($opt['value'] ?? '');
                                        $optLabel = (string) ($opt['label'] ?? '');
                                        $optBadge = $opt['badge'] ?? null;
                                    @endphp
                                    <button type="button"
                                        x-show="matches(@js($groupLabel . ' ' . $optLabel))"
                                        @click="selectOption(@js($opt['value']))"
                                        class="w-full px-4 py-3 text-left text-sm flex items-center justify-between gap-2 hover:bg-sky-50 dark:hover:bg-sky-500/10 transition cursor-pointer"
                                        :class="String(current) === @js($optVal) && @js($optVal !== '') ? 'bg-sky-50/80 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 font-semibold' : 'text-slate-700 dark:text-slate-200'">
                                        <span class="leading-snug">{{ $optLabel }}</span>
                                        <span class="flex items-center gap-1.5 shrink-0">
                                            @if ($optBadge)
                                                @php
                                                    $isZeroBadge = str_starts_with((string) $optBadge, '0 ');
                                                @endphp
                                                <span class="px-1.5 py-0.5 rounded-md text-xs font-bold {{ $isZeroBadge ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' : 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' }}">
                                                    {{ $optBadge }}
                                                </span>
                                            @endif
                                            <span class="material-symbols-rounded text-[18px]"
                                                :class="String(current) === @js($optVal) && @js($optVal !== '') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-300 dark:text-zinc-600'"
                                                x-text="String(current) === @js($optVal) && @js($optVal !== '') ? 'radio_button_checked' : 'radio_button_unchecked'"></span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </template>
</div>
