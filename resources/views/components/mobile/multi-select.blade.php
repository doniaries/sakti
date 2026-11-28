@props([
    'label' => null,
    'required' => false,
    'items' => [],
    'searchPlaceholder' => 'Cari nama atau jabatan...',
    'emptyText' => 'Tidak ada data yang cocok.',
    'allLabel' => 'Semua Pegawai',
    'clearLabel' => 'Batal Semua',
    'showSelectAll' => true,
    'maxHeight' => 'max-h-56',
])

@php
    $normalizedItems = collect($items)->map(function ($item) {
        if (is_array($item)) {
            $id = (string) ($item['id'] ?? '');
            $name = (string) ($item['label'] ?? '');
            $sublabel = (string) ($item['sublabel'] ?? '');
        } else {
            $id = (string) ($item->id ?? '');
            $name = (string) ($item->name ?? $item->label ?? '');
            $sublabel = (string) ($item->jabatan?->nama ?? $item->sublabel ?? '');
        }

        return [
            'id' => $id,
            'label' => $name,
            'sublabel' => $sublabel,
            'search' => mb_strtolower(trim($name . ' ' . $sublabel)),
        ];
    })->values()->all();

    $allIds = array_column($normalizedItems, 'id');
@endphp

<div x-data="{
        selectedIds: [],
        q: '',
        allIds: @js($allIds),
        get isAllSelected() {
            return this.allIds.length > 0 && this.selectedIds.length >= this.allIds.length;
        },
        toggleAll() {
            this.selectedIds = this.isAllSelected ? [] : [...this.allIds];
        },
        normalizeSelected() {
            if (!Array.isArray(this.selectedIds)) {
                this.selectedIds = [];
                return;
            }
            this.selectedIds = this.selectedIds.map(v => String(v));
        },
        init() {
            this.normalizeSelected();
            this.$watch('selectedIds', () => this.normalizeSelected());
        }
    }"
    x-modelable="selectedIds"
    {{ $attributes->whereStartsWith('wire:model') }}
    class="space-y-2">
    @if ($label || $showSelectAll)
        <div class="flex items-center justify-between gap-2">
            @if ($label)
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    {{ $label }}
                    @if ($required)
                        <span class="text-rose-500">*</span>
                    @endif
                </label>
            @endif
            <div class="flex items-center gap-1.5 shrink-0 ml-auto">
                @if ($showSelectAll)
                    <button type="button" @click="toggleAll()"
                        class="text-xs font-bold px-2.5 py-0.5 rounded-full border transition cursor-pointer"
                        :class="isAllSelected
                            ? 'bg-sky-600 border-sky-600 text-white'
                            : 'bg-white dark:bg-zinc-900 border-sky-300 dark:border-sky-500/40 text-sky-700 dark:text-sky-300 hover:bg-sky-50'">
                        <span x-text="isAllSelected ? @js($clearLabel) : @js($allLabel)"></span>
                    </button>
                @endif
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300">
                    <span x-text="selectedIds.length"></span> dipilih
                </span>
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 overflow-hidden">
        <div class="p-2 border-b border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-xs text-zinc-400"></i>
            <input type="search" x-model="q" placeholder="{{ $searchPlaceholder }}"
                class="w-full h-9 pl-8 pr-8 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 text-xs text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none">
            <button type="button" x-show="q.length > 0" x-cloak @click="q = ''"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <div class="{{ $maxHeight }} overflow-y-auto overscroll-contain p-1.5 space-y-0.5">
            @foreach ($normalizedItems as $item)
                <label x-show="!q.trim() || @js($item['search']).includes(q.trim().toLowerCase())"
                    class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-white dark:hover:bg-zinc-900 transition cursor-pointer">
                    <input type="checkbox" x-model="selectedIds" value="{{ $item['id'] }}"
                        class="rounded border-zinc-300 text-sky-600 focus:ring-sky-500 shrink-0">
                    <div class="min-w-0">
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $item['label'] }}</div>
                        @if ($item['sublabel'] !== '')
                            <div class="text-xs text-zinc-400 truncate">{{ $item['sublabel'] }}</div>
                        @endif
                    </div>
                </label>
            @endforeach
            @if (count($normalizedItems) === 0)
                <div class="py-4 text-center text-xs text-zinc-400">{{ $emptyText }}</div>
            @endif
        </div>
    </div>
</div>
