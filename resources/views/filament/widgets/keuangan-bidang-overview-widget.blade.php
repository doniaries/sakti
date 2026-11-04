@php
    $columns = $this->getColumns();
    $pollingInterval = $this->getPollingInterval();
    $canChooseBidang = $this->canChooseBidang();
    $daftarBidang = $this->getDaftarBidang();
    $activeBidang = $this->getActiveBidang();
    $selectedBidangId = $this->selectedBidangId;
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
    @if ($canChooseBidang && count($daftarBidang) > 1)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white/70 p-3 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-gray-900/70">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pilih Bidang:</span>
                <div class="inline-flex flex-wrap gap-1.5">
                    @foreach ($daftarBidang as $b)
                        <button
                            type="button"
                            wire:click="selectBidang({{ $b->id }})"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200 cursor-pointer {{ $selectedBidangId === $b->id ? 'bg-primary-600 text-white shadow' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20' }}"
                        >
                            {{ $b->nama }}
                        </button>
                    @endforeach
                </div>
            </div>
            @if ($activeBidang)
                <div class="text-xs text-gray-600 dark:text-gray-300">
                    PPTK: <span class="font-bold text-primary-600 dark:text-primary-400">{{ $activeBidang->kepala?->name ?? 'Belum Ditentukan' }}</span>
                </div>
            @endif
        </div>
    @endif

    {{ $this->content }}
</x-filament-widgets::widget>

