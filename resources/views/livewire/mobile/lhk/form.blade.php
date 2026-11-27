<div class="p-4 md:p-6 pb-28 max-w-3xl mx-auto space-y-5">
    @php
        $totalMenitLhk = collect($pekerjaans)->sum(fn($p) => (int) ($p['lama_menit'] ?? 0));
        $totalJamLhk = intdiv($totalMenitLhk, 60);
        $sisaMenitLhk = $totalMenitLhk % 60;
        $totalDurasiText =
            $totalMenitLhk > 0
                ? ($totalJamLhk > 0 && $sisaMenitLhk > 0
                    ? "{$totalJamLhk}j {$sisaMenitLhk}m"
                    : ($totalJamLhk > 0
                        ? "{$totalJamLhk} jam"
                        : "{$sisaMenitLhk} mnt"))
                : '0 jam';
    @endphp

    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('mobile.lhk.index') }}" wire:navigate
                class="w-10 h-10 rounded-xl bg-white dark:bg-[#132338] border border-slate-200/90 dark:border-slate-700/80 shadow-2xs flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-sky-600 hover:border-sky-300 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg md:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight truncate">
                    {{ $existingLhk ? 'Edit LHK' : 'Buat LHK' }}
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Laporan Harian Kinerja Pegawai</p>
            </div>
        </div>
        @if ($absenHariIni || $existingLhk)
            <div
                class="shrink-0 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-50 dark:bg-sky-500/15 border border-sky-200/80 dark:border-sky-500/30 text-sky-700 dark:text-sky-300 text-xs font-bold">
                <i class="fa-regular fa-clock text-[13px]"></i>
                <span>{{ count($pekerjaans) }} kegiatan</span>
                <span class="text-sky-300 dark:text-sky-600">•</span>
                <span class="font-mono">{{ $totalDurasiText }}</span>
            </div>
        @endif
    </div>

    <!-- Informasi Kehadiran -->
    <section
        class="bg-white dark:bg-[#132338]/95 rounded-2xl p-4 md:p-5 shadow-xs border border-slate-200/80 dark:border-slate-700/70 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <span
                    class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs">
                    <i class="fa-regular fa-calendar-check"></i>
                </span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">Informasi Kehadiran</h2>
            </div>
            @if ($bisaIsiLhk)
                <span
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 text-xs font-bold border border-emerald-200/70 dark:border-emerald-500/30">
                    <i class="fa-solid fa-circle-check text-[11px]"></i> Siap Diisi
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-6">
                <label
                    class="block text-[13px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tanggal
                    LHK</label>
                <x-mobile.date-picker wire:model.live="tanggal"
                    buttonClass="w-full bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-500/30 transition" />
            </div>

            @if ($absenHariIni || $existingLhk)
                <div
                    class="md:col-span-3 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200/70 dark:border-amber-500/25 px-3 py-2 flex md:flex-col items-center md:items-start justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Jam
                        Masuk</span>
                    <span class="text-base md:text-lg font-extrabold font-mono text-amber-700 dark:text-amber-300">
                        {{ $absenHariIni?->jam_masuk ?? $existingLhk?->jam_masuk ? substr((string) ($absenHariIni?->jam_masuk ?? $existingLhk?->jam_masuk), 0, 5) : '-' }}
                    </span>
                </div>
                <div
                    class="md:col-span-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-500/10 border border-emerald-200/70 dark:border-emerald-500/25 px-3 py-2 flex md:flex-col items-center md:items-start justify-between">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Jam
                        Pulang</span>
                    <span class="text-base md:text-lg font-extrabold font-mono text-emerald-700 dark:text-emerald-300">
                        {{ $absenHariIni?->jam_pulang ?? $existingLhk?->jam_pulang ? substr((string) ($absenHariIni?->jam_pulang ?? $existingLhk?->jam_pulang), 0, 5) : 'Belum' }}
                    </span>
                </div>
            @endif
        </div>

        @if (($absenHariIni || $existingLhk) && !$bisaIsiLhk)
            <div
                class="rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 p-3 text-xs flex items-center gap-2.5 border border-rose-200 dark:border-rose-500/30 font-semibold">
                <i class="fa-solid fa-triangle-exclamation shrink-0"></i>
                <span>Anda harus melakukan absen masuk (pagi) terlebih dahulu untuk mengisi LHK.</span>
            </div>
        @elseif ($absenHariIni || $existingLhk)
            <div
                class="rounded-xl bg-sky-50/80 dark:bg-sky-500/10 text-sky-800 dark:text-cyan-200 px-3.5 py-2.5 text-[13px] flex items-start gap-2.5 border border-sky-200/80 dark:border-sky-500/30 leading-relaxed">
                <i class="fa-solid fa-circle-info mt-0.5 text-sky-600 dark:text-cyan-400 shrink-0"></i>
                <span>
                    Mulai pukul <strong class="font-mono">{{ $waktuAwalKegiatan }} WIB</strong>.
                    Pilih <strong>Rencana Aksi</strong> lalu <strong>Aktivitas</strong> agar jam terisi otomatis.
                    @if (!($absenHariIni?->jam_pulang ?? $existingLhk?->jam_pulang))
                        Anda sudah dapat mengisi LHK walau belum absen pulang.
                    @endif
                </span>
            </div>
        @endif
    </section>

    @if (!$absenHariIni && !$existingLhk)
        @php
            $randomVector = collect([
                'images/vectors/kantor-1.png',
                'images/vectors/kantor-2.png',
                'images/vectors/perkampungan-adat.png',
            ])->random();
        @endphp
        <section class="rounded-3xl bg-white dark:bg-[#132338]/90 border border-slate-200/80 dark:border-slate-700/60 shadow-sm p-5 sm:p-6 text-center space-y-4">
            <div class="max-w-md mx-auto rounded-2xl {{ $isHariLibur ? 'bg-amber-50/80 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/30' : 'bg-rose-50/80 dark:bg-rose-500/10 border border-rose-200/80 dark:border-rose-500/30' }} px-4 py-3 text-left flex items-start gap-3">
                <span class="mt-0.5 w-8 h-8 rounded-xl {{ $isHariLibur ? 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300' : 'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-300' }} flex items-center justify-center shrink-0 text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div class="space-y-0.5">
                    <h3 class="text-xs sm:text-sm font-extrabold {{ $isHariLibur ? 'text-amber-900 dark:text-amber-200' : 'text-rose-900 dark:text-rose-200' }}">
                        {{ $isHariLibur ? 'Tanggal Tidak Masuk Kerja Resmi' : 'Belum Ada Data Absensi Masuk' }}
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        @if ($isHariLibur)
                            Tanggal <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong> merupakan
                            <strong>{{ $alasanNonKerja }}</strong>. LHK tidak perlu diisi pada tanggal ini.
                        @else
                            Anda belum melakukan absen masuk pada tanggal
                            <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>.
                            Silakan ambil presensi masuk terlebih dahulu sebelum mengisi LHK.
                        @endif
                    </p>
                </div>
            </div>

            <div class="max-w-[360px] mx-auto">
                <img src="{{ asset($randomVector) }}" alt="Ilustrasi Sijunjung" class="w-full h-auto object-contain mx-auto">
            </div>

            @if (!$isHariLibur)
                <a href="{{ route('mobile.absensi') }}" wire:navigate
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1B6CA8] hover:bg-[#155685] text-white text-xs font-bold shadow-sm active:scale-95 transition">
                    <i class="fa-solid fa-fingerprint"></i>
                    <span>Ambil Presensi Sekarang</span>
                </a>
            @endif
        </section>
    @endif

    @if ($absenHariIni || $existingLhk)
        <!-- Rincian Pekerjaan -->
        <section class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 dark:text-white">Rincian Pekerjaan</h2>
                    <p class="text-[13px] text-slate-500 dark:text-slate-400">Isi kegiatan harian sesuai Rencana Aksi
                        atau Umum</p>
                </div>
                <button type="button" wire:click="addPekerjaan" wire:loading.attr="disabled" wire:target="addPekerjaan"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 active:scale-95 px-3.5 py-2 rounded-xl shadow-xs transition cursor-pointer">
                    <i class="fa-solid fa-plus text-[13px]"></i>
                    <span>Tambah</span>
                </button>
            </div>

            @if (empty($pekerjaans))
                <div
                    class="bg-white dark:bg-[#132338]/95 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-8 text-center">
                    <div
                        class="w-12 h-12 mx-auto rounded-2xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-cyan-300 flex items-center justify-center text-lg mb-3">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Belum ada kegiatan.</p>
                    <p class="text-[13px] text-slate-400 mt-1">Ketuk tombol <strong>Tambah</strong> untuk mencatat
                        aktivitas.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($pekerjaans as $index => $pekerjaan)
                        <div wire:key="pekerjaan-{{ $index }}"
                            class="bg-white dark:bg-[#132338]/95 rounded-2xl p-4 md:p-5 shadow-xs border border-slate-200/85 dark:border-slate-700/75 space-y-4">
                            <!-- Card Header -->
                            <div
                                class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-sky-600 text-white text-[13px] font-extrabold">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="text-xs font-extrabold text-slate-800 dark:text-white">Kegiatan
                                        {{ $index + 1 }}</span>
                                </div>
                                <button type="button" wire:click="removePekerjaan({{ $index }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[13px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition cursor-pointer"
                                    title="Hapus kegiatan">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                    <span class="hidden sm:inline">Hapus</span>
                                </button>
                            </div>

                            <!-- 1. Rencana Aksi -->
                            <div>
                                <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">
                                    <span class="text-sky-600 dark:text-cyan-400">1.</span> Rencana Aksi
                                </label>
                                @php
                                    $aktCountByRa = collect($aktivitasList)
                                        ->whereNotNull('rencana_aksi_id')
                                        ->countBy('rencana_aksi_id');
                                    $umumAktCount = collect($aktivitasList)
                                        ->filter(
                                            fn($a) => is_null($a->rencana_aksi_id) ||
                                                (is_null($a->user_id) && is_null($a->jabatan_id)),
                                        )
                                        ->count();
                                    $raGroups = $rencanaAksiOptions
                                        ->map(
                                            fn($raItems, $subKegGroup) => [
                                                'label' => $subKegGroup,
                                                'options' => $raItems
                                                    ->map(
                                                        fn($ra) => [
                                                            'value' => $ra->id,
                                                            'label' => $ra->rencana_aksi,
                                                            'badge' => $aktCountByRa->get($ra->id, 0) . ' aktivitas',
                                                        ],
                                                    )
                                                    ->values()
                                                    ->all(),
                                            ],
                                        )
                                        ->values()
                                        ->all();
                                @endphp
                                <x-mobile.select :wire-model="'pekerjaans.' . $index . '.rencana_aksi_id'" :value="$pekerjaan['rencana_aksi_id'] ?? ''" empty-label="Umum"
                                    :empty-label="'Umum (' . $umumAktCount . ' aktivitas)'" search-placeholder="Cari rencana aksi..." :groups="$raGroups" />
                            </div>

                            <!-- 2. Aktivitas -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300">
                                        <span class="text-sky-600 dark:text-cyan-400">2.</span> Pilih Aktivitas (Bank
                                        Aktivitas)
                                    </label>
                                    <a href="{{ route('mobile.lhk.bank-aktivitas') }}" wire:navigate
                                        class="text-[13px] font-bold text-sky-600 dark:text-cyan-400 hover:underline inline-flex items-center gap-1">
                                        <i class="fa-solid fa-sliders text-xs"></i> Kelola Bank Aktivitas
                                    </a>
                                </div>
                                @php
                                    $selectedRaId = $pekerjaan['rencana_aksi_id'] ?? null;
                                    $selectedAktId = $pekerjaan['aktivitas_id'] ?? null;
                                    $filteredAktivitas = collect($aktivitasList)->filter(function ($a) use (
                                        $selectedRaId,
                                        $selectedAktId,
                                    ) {
                                        if ($selectedAktId && (int) $a->id === (int) $selectedAktId) {
                                            return true;
                                        }
                                        if (empty($selectedRaId)) {
                                            return true;
                                        }

                                        return (int) $a->rencana_aksi_id === (int) $selectedRaId;
                                    });
                                    $aktGroups = $filteredAktivitas
                                        ->groupBy(function ($a) {
                                            if (is_null($a->user_id) && is_null($a->jabatan_id)) {
                                                return 'Umum (Semua Pegawai)';
                                            }
                                            if ($a->rencanaAksi?->sasaran_sub_kegiatan === 'Tugas Tambahan') {
                                                return 'Tim Kreatif';
                                            }

                                            return $a->rencanaAksi?->sasaran_sub_kegiatan ?:
                                                ($a->bidang?->nama ?:
                                                    'Aktivitas Pegawai');
                                        })
                                        ->map(
                                            fn($aktItems, $aktGroup) => [
                                                'label' => $aktGroup,
                                                'options' => $aktItems
                                                    ->map(function ($a) {
                                                        $jamLabel = $a->menit
                                                            ? str_replace(
                                                                    '.',
                                                                    ',',
                                                                    (string) (float) round($a->menit / 60, 2),
                                                                ) . ' jam'
                                                            : null;

                                                        return [
                                                            'value' => $a->id,
                                                            'label' => $a->nama_aktivitas ?? $a->nama,
                                                            'badge' => $jamLabel,
                                                        ];
                                                    })
                                                    ->values()
                                                    ->all(),
                                            ],
                                        )
                                        ->values()
                                        ->all();
                                @endphp
                                <x-mobile.select :wire-model="'pekerjaans.' . $index . '.aktivitas_id'" :value="$pekerjaan['aktivitas_id'] ?? ''"
                                    placeholder="- Pilih Aktivitas (Jam Terisi Otomatis) -"
                                    empty-label="- Pilih Aktivitas (Jam Terisi Otomatis) -"
                                    search-placeholder="Cari aktivitas..." :groups="$aktGroups" />
                            </div>

                            <!-- Jam Mulai, Jam Selesai, Lama -->
                            <div class="grid grid-cols-3 gap-2.5">
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Jam
                                        Mulai</label>
                                    <x-mobile.time-picker wire:model.live="pekerjaans.{{ $index }}.jam_mulai"
                                        buttonClass="w-full bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-mono font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500" />
                                    @error('pekerjaans.' . $index . '.jam_mulai')
                                        <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Jam
                                        Selesai</label>
                                    <x-mobile.time-picker wire:model.live="pekerjaans.{{ $index }}.jam_selesai"
                                        buttonClass="w-full bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-2 text-xs font-mono font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500" />
                                    @error('pekerjaans.' . $index . '.jam_selesai')
                                        <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Durasi</label>
                                    <div
                                        class="w-full bg-sky-50 dark:bg-sky-500/15 border border-sky-200/80 dark:border-sky-500/30 rounded-xl px-2.5 py-2 text-xs font-mono font-extrabold text-sky-700 dark:text-cyan-300 text-center">
                                        {{ str_replace('.', ',', (string) (float) round(((int) ($pekerjaan['lama_menit'] ?? 0)) / 60, 2)) }}
                                        jam
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Hasil Pekerjaan -->
                            <div>
                                <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">
                                    <span class="text-sky-600 dark:text-cyan-400">3.</span> Hasil Pekerjaan
                                </label>
                                <textarea wire:model="pekerjaans.{{ $index }}.hasil_pekerjaan" rows="2"
                                    placeholder="Uraikan hasil pekerjaan yang dilaksanakan..."
                                    class="w-full bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs md:text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition"></textarea>
                                @error('pekerjaans.' . $index . '.hasil_pekerjaan')
                                    <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- 4. Bukti Google Drive (2 Cara: Kamera Langsung ke Drive / Tempel Link) -->
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300">
                                        <span class="text-sky-600 dark:text-cyan-400">4.</span> Bukti Kegiatan Google Drive
                                        (Opsional)
                                    </label>
                                    @if (
                                        !empty($pekerjaan['bukti_url']) &&
                                            in_array(strtolower((string) parse_url($pekerjaan['bukti_url'], PHP_URL_HOST)),
                                                ['drive.google.com', 'docs.google.com'],
                                                true))
                                        <a href="{{ $pekerjaan['bukti_url'] }}" target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-[13px] font-bold text-sky-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
                                            <i class="fa-brands fa-google-drive"></i> Buka Drive
                                        </a>
                                    @endif
                                </div>

                                <!-- Cara 1: Kamera HP -> Upload ke Google Drive Sendiri -->
                                <div
                                    class="rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-[#0F1E33]/80 p-3 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <span
                                            class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Cara 1 • Kamera HP ke Google Drive
                                        </span>
                                        @if (auth()->user()?->hasGoogleDriveConnected())
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                                                <i class="fa-solid fa-link text-[11px]"></i> Drive Terhubung
                                            </span>
                                        @endif
                                    </div>

                                    @if (auth()->user()?->hasGoogleDriveConnected())
                                        <div class="flex flex-wrap items-center gap-2">
                                            <label
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-2xs transition cursor-pointer">
                                                <i class="fa-solid fa-camera"></i>
                                                <span>Ambil Foto / Pilih Gambar</span>
                                                <input type="file" accept="image/*" capture="environment"
                                                    wire:model="evidencePhotos.{{ $index }}" class="hidden">
                                            </label>
                                            <a href="{{ route('auth.google.drive', ['return_url' => route('mobile.lhk.create', ['tanggal' => $tanggal])]) }}"
                                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-sky-600 underline">
                                                Ganti akun Drive
                                            </a>
                                        </div>
                                        <div wire:loading wire:target="evidencePhotos.{{ $index }}"
                                            class="text-[13px] text-sky-600 dark:text-cyan-400 flex items-center gap-1.5 font-semibold">
                                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                                            <span>Mengunggah foto ke folder
                                                <strong>sakti-lhk-evidence-{{ $tanggal }}</strong> &amp; membuka akses
                                                publik...</span>
                                        </div>
                                        @error('evidencePhotos.' . $index)
                                            <span class="text-rose-500 text-xs font-semibold block">{{ $message }}</span>
                                        @enderror
                                    @else
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-[13px] text-slate-600 dark:text-slate-300 leading-snug">
                                                Hubungkan Google Drive agar foto kamera otomatis tersimpan di folder
                                                <strong class="font-mono">sakti-lhk-evidence-{{ $tanggal }}</strong>.
                                            </p>
                                            <a href="{{ route('auth.google.drive', ['return_url' => route('mobile.lhk.create', ['tanggal' => $tanggal])]) }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-sky-400 hover:text-sky-600 shadow-2xs transition">
                                                <i class="fa-brands fa-google-drive text-sky-600"></i>
                                                <span>Hubungkan Google Drive</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Cara 2: Tempel Link Google Drive Manual -->
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                        Cara 2 • Atau Tempel Link Google Drive
                                    </label>
                                    <input type="url"
                                        wire:model.live.debounce.500ms="pekerjaans.{{ $index }}.bukti_url"
                                        placeholder="https://drive.google.com/file/d/..."
                                        class="w-full bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs md:text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition">
                                    <div wire:loading wire:target="pekerjaans.{{ $index }}.bukti_url"
                                        class="text-xs text-sky-600 dark:text-cyan-400 mt-1 flex items-center gap-1 font-medium">
                                        <i class="fa-solid fa-circle-notch fa-spin"></i> Memeriksa akses publik Google
                                        Drive...
                                    </div>
                                    @error('pekerjaans.' . $index . '.bukti_url')
                                        <span
                                            class="text-rose-500 text-xs font-semibold block mt-1">{{ $message }}</span>
                                    @else
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                            Pastikan akses berbagi Google Drive sudah diubah ke <strong>Siapa saja yang
                                                memiliki link</strong>.
                                        </p>
                                    @enderror
                                </div>

                                @php
                                    $previewUrl = $this->getGoogleDrivePreviewUrl($pekerjaan['bukti_url'] ?? null);
                                @endphp
                                @if (!empty($pekerjaan['bukti_url']) && !$previewUrl)
                                    <p class="text-xs text-rose-500 mt-1.5">
                                        Link harus berupa tautan file/folder Google Drive (`drive.google.com` atau
                                        `docs.google.com`).
                                    </p>
                                @elseif ($previewUrl)
                                    <div
                                        class="mt-2.5 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#132338]">
                                        <iframe src="{{ $previewUrl }}" class="w-full h-48 border-0"
                                            loading="lazy" allowfullscreen></iframe>
                                        <div
                                            class="px-3 py-2 bg-amber-50 dark:bg-amber-500/10 border-t border-amber-200 dark:border-amber-500/20 text-xs text-amber-800 dark:text-amber-300">
                                            Jika preview tidak tampak, pastikan akses file/folder Google Drive diubah ke
                                            <strong>Siapa saja yang memiliki link</strong>.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" wire:click="addPekerjaan" wire:loading.attr="disabled"
                    wire:target="addPekerjaan"
                    class="w-full py-3 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-sky-400 hover:text-sky-600 dark:hover:text-cyan-300 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus text-[13px]"></i> Tambah Kegiatan Lain
                </button>
            @endif
        </section>

        <!-- Simpan -->
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
            @disabled(!$bisaIsiLhk)
            class="w-full bg-gradient-to-r from-[#1B6CA8] to-sky-600 hover:from-[#155685] hover:to-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-sm py-4 rounded-2xl shadow-lg shadow-sky-600/20 active:scale-[0.99] transition flex justify-center items-center gap-2 cursor-pointer">
            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
                LHK</span>
            <span wire:loading wire:target="save"><i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>
                Menyimpan...</span>
        </button>
    @endif
</div>
