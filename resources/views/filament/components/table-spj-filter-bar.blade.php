@php
    /** @var \App\Models\User|null $user */
    $user = \Illuminate\Support\Facades\Auth::user();

    $canChooseBidang = $user !== null && (
        $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara']) ||
        blank($user->bidang_id) ||
        $user->can('viewAny', \App\Models\Bidang::class)
    );

    $daftarBidang = $canChooseBidang
        ? \App\Models\Bidang::with('kepala:id,name,nip')->orderBy('nama')->get()
        : ($user && filled($user->bidang_id) ? \App\Models\Bidang::with('kepala:id,name,nip')->whereKey($user->bidang_id)->get() : collect());

    $activeBidang = null;
    $selectedBidangId = request()->query('bidang')
        ?? data_get(request()->query('filters'), 'bidang_id.value')
        ?? data_get(request()->query('tableFilters'), 'bidang_id.value');

    $rawBulan = request()->query('bulan');
    $selectedTanggal = request()->query('tanggal');

    if (request()->is('livewire/*') && request()->header('Referer')) {
        $refererQuery = [];
        parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
        if (! request()->has('bidang')) {
            $selectedBidangId = $selectedBidangId ?? ($refererQuery['bidang']
                ?? data_get($refererQuery, 'filters.bidang_id.value')
                ?? data_get($refererQuery, 'tableFilters.bidang_id.value'));
        }
        if (! request()->has('bulan')) {
            $rawBulan = $rawBulan ?? ($refererQuery['bulan'] ?? null);
        }
        if (! request()->has('tanggal')) {
            $selectedTanggal = $selectedTanggal ?? ($refererQuery['tanggal'] ?? null);
        }
    }

    // Default bulan adalah bulan aktif saat ini (misal: November) kecuali pengguna memilih 'all'
    if (filled($selectedTanggal)) {
        $selectedBulan = (int) date('n', strtotime($selectedTanggal));
    } else {
        $selectedBulan = match (true) {
            $rawBulan === 'all' => null,
            filled($rawBulan) => (int) $rawBulan,
            default => (int) date('n'),
        };
    }

    if (filled($selectedBidangId)) {
        $activeBidang = \App\Models\Bidang::find($selectedBidangId);
    } elseif (! $canChooseBidang && $user && filled($user->bidang_id)) {
        $activeBidang = \App\Models\Bidang::find($user->bidang_id);
    }

    $selectedTahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();

    $daftarBulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $cleanUrl = \App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource::getUrl('index');
@endphp

<div class="flex flex-wrap items-center gap-2.5 text-xs not-italic font-normal">
    {{-- Filter Bidang --}}
    <div class="flex items-center gap-1.5">
        <span class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bidang:</span>
        @if ($canChooseBidang && count($daftarBidang) > 1)
            <select
                id="table-filter-spj-bidang"
                onchange="applyTableSpjFilter()"
                class="h-8 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-gray-800 shadow-xs transition hover:border-primary-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 cursor-pointer"
            >
                <option value="" {{ empty($selectedBidangId) ? 'selected' : '' }}>Semua Bidang</option>
                @foreach ($daftarBidang as $b)
                    <option value="{{ $b->id }}" {{ (int) $selectedBidangId === (int) $b->id ? 'selected' : '' }}>
                        {{ $b->nama }}
                    </option>
                @endforeach
            </select>
        @elseif ($activeBidang)
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800 dark:bg-white/10 dark:text-gray-200">
                <span class="font-bold text-primary-600 dark:text-primary-400">{{ $activeBidang->nama }}</span>
            </span>
        @endif
    </div>

    {{-- Divider --}}
    <div class="hidden h-5 w-px bg-gray-200 sm:block dark:bg-gray-700"></div>

    {{-- Filter Bulan --}}
    <div class="flex items-center gap-1.5">
        <span class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bulan:</span>
        <select
            id="table-filter-spj-bulan"
            onchange="applyTableSpjFilter()"
            class="h-8 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-gray-800 shadow-xs transition hover:border-primary-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 cursor-pointer"
        >
            <option value="all" {{ $selectedBulan === null ? 'selected' : '' }}>Semua Bulan</option>
            @foreach ($daftarBulan as $num => $nama)
                <option value="{{ $num }}" {{ (int) $selectedBulan === (int) $num ? 'selected' : '' }}>
                    {{ $nama }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Divider --}}
    <div class="hidden h-5 w-px bg-gray-200 sm:block dark:bg-gray-700"></div>

    {{-- Filter Tanggal Spesifik (Non-Native Alpine.js Calendar Popover) --}}
    <div
        class="relative flex items-center gap-1.5"
        x-data="{
            open: false,
            tahun: {{ (int) $selectedTahun }},
            selected: '{{ $selectedTanggal }}',
            viewMonth: {{ filled($selectedTanggal) ? (int) \Carbon\Carbon::parse($selectedTanggal)->month - 1 : (filled($selectedBulan) ? (int) $selectedBulan - 1 : now()->month - 1) }},
            months: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
            daysHeader: ['Sen','Sel','Rab','Kam','Jum','Sab','Min'],
            get daysInMonth() {
                return new Date(this.tahun, this.viewMonth + 1, 0).getDate();
            },
            get startOffset() {
                const d = new Date(this.tahun, this.viewMonth, 1).getDay();
                return d === 0 ? 6 : d - 1;
            },
            formatDisplay(val) {
                if (!val) return 'Pilih Tanggal';
                const [y, m, d] = val.split('-');
                return `${d}/${m}/${y}`;
            },
            selectDate(day) {
                const m = String(this.viewMonth + 1).padStart(2, '0');
                const d = String(day).padStart(2, '0');
                this.selected = `${this.tahun}-${m}-${d}`;
                this.open = false;
                const input = document.getElementById('table-filter-spj-tanggal');
                if (input) {
                    input.value = this.selected;
                    applyTableSpjFilter();
                }
            },
            clearDate() {
                this.selected = '';
                this.open = false;
                const input = document.getElementById('table-filter-spj-tanggal');
                if (input) {
                    input.value = '';
                    applyTableSpjFilter();
                }
            },
            isSelected(day) {
                const m = String(this.viewMonth + 1).padStart(2, '0');
                const d = String(day).padStart(2, '0');
                return this.selected === `${this.tahun}-${m}-${d}`;
            }
        }"
        @click.outside="open = false"
    >
        <span @click="open = !open" class="cursor-pointer font-bold uppercase tracking-wider text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">Tanggal:</span>
        <button
            type="button"
            @click="open = !open"
            class="inline-flex h-8 cursor-pointer items-center gap-2 rounded-lg border border-gray-300 bg-white px-2.5 text-xs font-semibold shadow-xs transition hover:border-primary-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800"
        >
            <svg class="h-3.5 w-3.5 shrink-0 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
            </svg>
            <span x-text="formatDisplay(selected)" :class="selected ? 'text-gray-800 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'"></span>
        </button>

        <input type="hidden" id="table-filter-spj-tanggal" value="{{ $selectedTanggal }}" />

        {{-- Popover Kalender --}}
        <div
            x-show="open"
            x-transition.opacity.duration.150ms
            x-cloak
            class="absolute left-0 top-10 z-50 w-64 rounded-xl border border-gray-200 bg-white p-3 shadow-lg dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="mb-2 flex items-center justify-between">
                <button
                    type="button"
                    @click="viewMonth = viewMonth > 0 ? viewMonth - 1 : 11"
                    class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                </button>
                <span class="text-xs font-bold text-gray-800 dark:text-gray-100" x-text="months[viewMonth] + ' ' + tahun"></span>
                <button
                    type="button"
                    @click="viewMonth = viewMonth < 11 ? viewMonth + 1 : 0"
                    class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            <div class="mb-1 grid grid-cols-7 text-center text-[10px] font-semibold text-gray-400">
                <template x-for="d in daysHeader" :key="d">
                    <div class="py-1" x-text="d"></div>
                </template>
            </div>

            <div class="grid grid-cols-7 gap-0.5 text-center text-xs">
                <template x-for="blank in startOffset" :key="'b'+blank">
                    <div></div>
                </template>
                <template x-for="day in daysInMonth" :key="day">
                    <button
                        type="button"
                        @click="selectDate(day)"
                        x-text="day"
                        :class="isSelected(day)
                            ? 'bg-primary-600 font-bold text-white shadow-xs'
                            : 'text-gray-700 hover:bg-primary-50 hover:text-primary-600 dark:text-gray-200 dark:hover:bg-gray-800'"
                        class="flex h-7 w-7 items-center justify-center rounded-lg transition"
                    ></button>
                </template>
            </div>

            <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
                <button type="button" @click="clearDate()" class="text-[11px] font-semibold text-red-500 hover:underline">Hapus</button>
                <button type="button" @click="viewMonth = {{ now()->month - 1 }}; selectDate({{ now()->day }})" class="text-[11px] font-semibold text-primary-600 hover:underline dark:text-primary-400">Hari Ini</button>
            </div>
        </div>
    </div>

    {{-- Tombol Reset Filter --}}
    @if (filled($selectedBidangId) || filled($selectedBulan) || filled($selectedTanggal))
        <a
            href="{{ $cleanUrl }}"
            title="Reset Filter"
            class="inline-flex h-8 items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/40 dark:text-red-400"
        >
            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <span>Reset</span>
        </a>
    @endif

    {{-- Divider --}}
    <div class="hidden h-5 w-px bg-gray-200 sm:block dark:bg-gray-700"></div>

    {{-- Tahun Anggaran Aktif --}}
    <div class="flex items-center gap-1.5">
        <span class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tahun:</span>
        <span class="inline-flex items-center gap-1 rounded-lg border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800 shadow-2xs dark:border-amber-700/50 dark:bg-amber-950/40 dark:text-amber-300">
            <svg class="h-3 w-3 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
            </svg>
            TA {{ $selectedTahun }}
        </span>
    </div>
</div>

<script>
    function openSpjDatePicker(el) {
        if (!el) return;
        if (typeof el.showPicker === 'function') {
            try {
                el.showPicker();
            } catch (e) {}
        }
    }

    function applyTableSpjFilter() {
        const url = new URL(window.location.href);
        const bidangEl = document.getElementById('table-filter-spj-bidang');
        const bulanEl = document.getElementById('table-filter-spj-bulan');
        const tanggalEl = document.getElementById('table-filter-spj-tanggal');

        if (bidangEl && bidangEl.value) {
            url.searchParams.set('bidang', bidangEl.value);
        } else if (bidangEl) {
            url.searchParams.delete('bidang');
        }

        if (bulanEl && bulanEl.value) {
            url.searchParams.set('bulan', bulanEl.value);
            // Jika tanggal yang dipilih berada di bulan yang berbeda, reset tanggal
            if (tanggalEl && tanggalEl.value && bulanEl.value !== 'all') {
                const parts = tanggalEl.value.split('-');
                if (parts.length === 3 && parseInt(parts[1], 10) !== parseInt(bulanEl.value, 10)) {
                    url.searchParams.delete('tanggal');
                }
            }
        } else {
            url.searchParams.delete('bulan');
        }

        if (tanggalEl && tanggalEl.value) {
            url.searchParams.set('tanggal', tanggalEl.value);
            // Otomatis sinkronkan bulan dengan bulan dari tanggal yang dipilih
            const parts = tanggalEl.value.split('-');
            if (parts.length === 3) {
                const m = parseInt(parts[1], 10);
                if (m >= 1 && m <= 12) {
                    url.searchParams.set('bulan', m);
                }
            }
        } else {
            url.searchParams.delete('tanggal');
        }

        url.searchParams.delete('page');

        window.location.href = url.toString();
    }
</script>

