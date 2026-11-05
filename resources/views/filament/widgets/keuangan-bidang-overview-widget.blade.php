@php
    $columns = $this->getColumns();
    $pollingInterval = $this->getPollingInterval();
    $canChooseBidang = $this->canChooseBidang();
    $daftarBidang = $this->getDaftarBidang();
    $activeBidang = $this->getActiveBidang();
    $selectedBidangId = $this->selectedBidangId;
    $selectedTahun = $this->selectedTahun ?? (int) date('Y');
    $cleanPageUrl = $this->getCleanPageUrl();

    $currentYear = (int) date('Y');
    $daftarTahun = [
        $currentYear - 2,
        $currentYear - 1,
        $currentYear,
        $currentYear + 1,
        $currentYear + 2,
    ];
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

    {{-- Filter Bidang & Tahun Anggaran (Diposisikan di bawah widget card, di atas tombol akses) --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white/80 p-3 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-gray-900/80">
        {{-- Bagian Kiri: Filter Bidang --}}
        <div class="flex flex-wrap items-center gap-2">
            @if ($canChooseBidang && count($daftarBidang) > 1)
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bidang:</span>
                <div class="inline-flex flex-wrap gap-1.5">
                    @php
                        $urlSemuaBidang = $cleanPageUrl . '?' . http_build_query(array_filter(['tahun' => $selectedTahun]));
                    @endphp
                    <a
                        href="{{ $urlSemuaBidang }}"
                        wire:navigate
                        class="rounded-lg px-2.5 py-1 text-xs font-semibold transition-all duration-200 cursor-pointer {{ empty($selectedBidangId) ? 'bg-primary-600 text-white shadow' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20' }}"
                    >
                        Semua Bidang
                    </a>
                    @foreach ($daftarBidang as $b)
                        @php
                            $targetUrl = $cleanPageUrl . '?' . http_build_query(array_filter([
                                'bidang' => $b->id,
                                'tahun' => $selectedTahun,
                            ]));
                        @endphp
                        <a
                            href="{{ $targetUrl }}"
                            wire:navigate
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold transition-all duration-200 cursor-pointer {{ (int) $selectedBidangId === (int) $b->id ? 'bg-primary-600 text-white shadow' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20' }}"
                        >
                            {{ $b->nama }}
                        </a>
                    @endforeach
                </div>
            @elseif ($activeBidang)
                <div class="text-xs font-medium text-gray-600 dark:text-gray-300">
                    Bidang: <span class="font-bold text-primary-600 dark:text-primary-400">{{ $activeBidang->nama }}</span> (PPTK: {{ $activeBidang->kepala?->name ?? 'Belum Ditentukan' }})
                </div>
            @endif
        </div>

        {{-- Bagian Kanan: Filter Tombol Dropdown "Pilih Tahun" Sesuai Lampiran --}}
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun Anggaran:</span>
            <div class="relative inline-block">
                <select
                    onchange="if(this.value) { window.location.href = '{{ $cleanPageUrl }}?' + new URLSearchParams({@if($selectedBidangId) bidang: '{{ $selectedBidangId }}', @endif tahun: this.value}).toString(); }"
                    class="h-8 rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-800 shadow-sm transition hover:border-primary-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 cursor-pointer"
                >
                    <option value="" disabled>Pilih Tahun</option>
                    @foreach ($daftarTahun as $thn)
                        <option value="{{ $thn }}" {{ $selectedTahun === $thn ? 'selected' : '' }}>
                            Tahun {{ $thn }} {{ $thn === (int) date('Y') ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
