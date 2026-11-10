@php
    $columns = $this->getColumns();
    $pollingInterval = $this->getPollingInterval();
    $canChooseBidang = $this->canChooseBidang();
    $daftarBidang = $this->getDaftarBidang();
    $activeBidang = $this->getActiveBidang();
    $selectedBidangId = $this->selectedBidangId;
    $selectedTahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();
    $cleanPageUrl = $this->getCleanPageUrl();
@endphp

<x-filament-widgets::widget
    :attributes="
        (new \Illuminate\View\ComponentAttributeBag)
            ->merge([
                'wire:poll.' . $pollingInterval => $pollingInterval ? true : null,
            ], escape: false)
            ->class([
                'fi-wi-stats-overview',
            ])
    "
>
    {{-- Kartu Stats Overview --}}
    {{ $this->content }}

    {{-- Filter Bidang (Select) & Tahun Anggaran (Terkunci Global) --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white/80 p-3 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-gray-900/80">
        {{-- Bagian Kiri: Filter Bidang (Select Dropdown) --}}
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bidang:</span>
            @if ($canChooseBidang && count($daftarBidang) > 1)
                <div class="relative inline-block">
                    <select
                        onchange="window.location.href = '{{ $cleanPageUrl }}' + (this.value ? '?bidang=' + this.value : '');"
                        class="h-8 rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-800 shadow-sm transition hover:border-primary-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 cursor-pointer"
                    >
                        <option value="" {{ empty($selectedBidangId) ? 'selected' : '' }}>Semua Bidang</option>
                        @foreach ($daftarBidang as $b)
                            <option value="{{ $b->id }}" {{ (int) $selectedBidangId === (int) $b->id ? 'selected' : '' }}>
                                {{ $b->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @elseif ($activeBidang)
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800 dark:bg-white/10 dark:text-gray-200">
                    <span class="font-bold text-primary-600 dark:text-primary-400">{{ $activeBidang->nama }}</span>
                    @if ($activeBidang->kepala?->name)
                        <span class="text-gray-500 dark:text-gray-400">({{ $activeBidang->kepala->name }})</span>
                    @endif
                </span>
            @endif
        </div>

        {{-- Bagian Kanan: Tahun Anggaran Aktif (Terkunci Sesuai Pengaturan Sistem) --}}
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun Anggaran:</span>
            <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800 shadow-xs dark:border-amber-700/50 dark:bg-amber-950/40 dark:text-amber-300">
                <svg class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                </svg>
                Tahun {{ $selectedTahun }} (Aktif)
            </span>
        </div>
    </div>
</x-filament-widgets::widget>
