@php
    $moduleItems = [
        'lhk' => ['label' => 'LHK, Bank Aktivitas & Reputasi', 'desc' => 'Menu LHK, Bank Aktivitas, dan Reputasi pegawai'],
        'izin' => ['label' => 'Izin, Sakit & Cuti', 'desc' => 'Pengajuan & riwayat Izin, Sakit, Cuti'],
        'wfh' => ['label' => 'WFH (Work From Home)', 'desc' => 'Menu presensi & pengajuan WFH'],
        'surat_tugas' => ['label' => 'Surat Tugas Tim Kreatif', 'desc' => 'Pembuatan & persetujuan Surat Tugas'],
        'spj' => ['label' => 'Keuangan & SPJ', 'desc' => 'Modul pengajuan & verifikasi SPJ'],
    ];
@endphp

<div class="p-4 pb-28 text-slate-800 dark:text-slate-100">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('mobile.profil') }}" wire:navigate
            class="w-9 h-9 rounded-xl bg-white dark:bg-white/10 border border-slate-200 dark:border-white/15 flex items-center justify-center text-slate-600 dark:text-slate-200">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <div>
            <h1 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">Pengaturan Modul Sistem</h1>
            <p class="text-[13px] text-slate-500 dark:text-slate-400">Khusus Super Admin • Web, Mobile & Android</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#132338] rounded-3xl p-5 shadow-xs border border-slate-200/80 dark:border-slate-700/70 space-y-3">
        @foreach ($moduleItems as $modKey => $modMeta)
            @php $enabled = (bool) ($modulAktif[$modKey] ?? false); @endphp
            <div wire:key="mod-{{ $modKey }}" class="flex items-center justify-between gap-3 py-3 border-b border-slate-100 dark:border-slate-700/60 last:border-b-0">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 dark:text-white">{{ $modMeta['label'] }}</p>
                    <p class="text-[13px] text-slate-400 leading-snug mt-0.5">{{ $modMeta['desc'] }}</p>
                </div>
                <button type="button" wire:click="toggleModul('{{ $modKey }}')"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out {{ $enabled ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"
                    role="switch" aria-checked="{{ $enabled ? 'true' : 'false' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        @endforeach
    </div>
</div>
