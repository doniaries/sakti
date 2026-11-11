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

    if (blank($selectedBidangId) && request()->header('Referer')) {
        $refererQuery = [];
        parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
        $selectedBidangId = $refererQuery['bidang']
            ?? data_get($refererQuery, 'filters.bidang_id.value')
            ?? data_get($refererQuery, 'tableFilters.bidang_id.value');
    }

    if (filled($selectedBidangId)) {
        $activeBidang = \App\Models\Bidang::find($selectedBidangId);
    } elseif (! $canChooseBidang && $user && filled($user->bidang_id)) {
        $activeBidang = \App\Models\Bidang::find($user->bidang_id);
    }

    $selectedBulan = request()->query('bulan');
    if (blank($selectedBulan) && request()->header('Referer')) {
        $refererQuery = [];
        parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
        $selectedBulan = $refererQuery['bulan'] ?? null;
    }

    $selectedTanggal = request()->query('tanggal');
    if (blank($selectedTanggal) && request()->header('Referer')) {
        $refererQuery = [];
        parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
        $selectedTanggal = $refererQuery['tanggal'] ?? null;
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
            <option value="" {{ empty($selectedBulan) ? 'selected' : '' }}>Semua Bulan</option>
            @foreach ($daftarBulan as $num => $nama)
                <option value="{{ $num }}" {{ (int) $selectedBulan === (int) $num ? 'selected' : '' }}>
                    {{ $nama }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Divider --}}
    <div class="hidden h-5 w-px bg-gray-200 sm:block dark:bg-gray-700"></div>

    {{-- Filter Tanggal Spesifik (Klik langsung buka kalender tanpa harus klik ikon kalender) --}}
    <div class="flex items-center gap-1.5" onclick="openSpjDatePicker(document.getElementById('table-filter-spj-tanggal'))">
        <span class="cursor-pointer font-bold uppercase tracking-wider text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">Tanggal:</span>
        <div class="relative inline-flex items-center">
            <input
                type="date"
                id="table-filter-spj-tanggal"
                value="{{ $selectedTanggal }}"
                min="{{ $selectedTahun }}-01-01"
                max="{{ $selectedTahun }}-12-31"
                onchange="applyTableSpjFilter()"
                onclick="openSpjDatePicker(this)"
                onkeydown="if(event.key === 'Enter' || event.key === ' ') { openSpjDatePicker(this); }"
                class="h-8 cursor-pointer rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-gray-800 shadow-xs transition hover:border-primary-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            />
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
        } else {
            url.searchParams.delete('bulan');
        }

        if (tanggalEl && tanggalEl.value) {
            url.searchParams.set('tanggal', tanggalEl.value);
        } else {
            url.searchParams.delete('tanggal');
        }

        url.searchParams.delete('page');

        window.location.href = url.toString();
    }
</script>
