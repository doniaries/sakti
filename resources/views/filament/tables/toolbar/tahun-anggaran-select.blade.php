<div class="inline-flex items-center gap-2 py-0.5" wire:key="toolbar-select-tahun">
    <label class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
        <x-heroicon-m-calendar-days class="h-4 w-4 text-primary-500" />
        <span>Tahun:</span>
    </label>
    <select
        wire:model.live="tableFilters.tahun_anggaran.value"
        class="h-8 rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-800 shadow-sm transition hover:border-primary-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 cursor-pointer"
    >
        @php
            $currentYear = (int) date('Y');
            $maxYear = $currentYear + 1; // Batasi maksimal ke 2027 jika sekarang 2026
            $minYear = $currentYear - 2;

            $years = [];
            for ($y = $maxYear; $y >= $minYear; $y--) {
                $years[$y] = (string) $y;
            }
        @endphp
        @foreach ($years as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
