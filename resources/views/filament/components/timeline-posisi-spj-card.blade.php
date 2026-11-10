@php
    /** @var \App\Models\PengajuanSpj|null $record */
    $record = $getRecord() ?? ($record ?? null);
    if (! $record) {
        return;
    }

    $status = $record->status;

    // Mapping 6 steps
    $steps = [
        1 => ['label' => 'Pemohon', 'actor' => $record->user?->name],
        2 => ['label' => 'PPTK', 'actor' => $record->pptk?->name],
        3 => ['label' => 'PPK', 'actor' => $record->ppk?->name],
        4 => ['label' => 'PA (Kadis)', 'actor' => $record->pa?->name],
        5 => ['label' => 'Bendahara', 'actor' => $record->bendahara?->name],
        6 => ['label' => 'Cair / Selesai', 'actor' => $record->tanggal_dicairkan ? $record->tanggal_dicairkan->format('d/m/Y') : null],
    ];

    $stepStates = [
        1 => match ($status) {
            'draft' => 'active',
            default => 'completed',
        },
        2 => match ($status) {
            'draft' => 'pending',
            'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk' => 'active',
            'revisi_pptk' => 'revision',
            default => 'completed',
        },
        3 => match ($status) {
            'draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk' => 'pending',
            'menunggu_verifikasi_ppk' => 'active',
            'revisi_ppk' => 'revision',
            default => 'completed',
        },
        4 => match ($status) {
            'menunggu_persetujuan_pa' => 'active',
            'revisi_pa' => 'revision',
            'menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan' => 'completed',
            default => 'pending',
        },
        5 => match ($status) {
            'menunggu_verifikasi_bendahara', 'disetujui' => 'active',
            'revisi_bendahara' => 'revision',
            'dicairkan' => 'completed',
            default => 'pending',
        },
        6 => match ($status) {
            'dicairkan' => 'completed',
            default => 'pending',
        },
    ];

    $heroTitle = match ($status) {
        'draft' => 'Draf Pesanan (Pemohon)',
        'diajukan' => 'Meja PPTK (Menunggu TTD Surat Pesanan)',
        'menunggu_realisasi' => 'Pemohon (Proses Realisasi Belanja & SPJ)',
        'menunggu_verifikasi_pptk' => 'Meja PPTK (Verifikasi Bukti Belanja SPJ)',
        'revisi_pptk' => 'Perlu Revisi (Dikembalikan oleh PPTK)',
        'menunggu_verifikasi_ppk' => 'Meja PPK (Verifikasi Keuangan & Pajak)',
        'revisi_ppk' => 'Perlu Revisi (Dikembalikan oleh PPK)',
        'menunggu_persetujuan_pa' => 'Meja PA / Kepala Dinas (Persetujuan NPD)',
        'revisi_pa' => 'Perlu Revisi (Dikembalikan oleh PA)',
        'menunggu_verifikasi_bendahara' => 'Meja Bendahara (Pengujian Kas & SP2D)',
        'revisi_bendahara' => 'Perlu Revisi (Dikembalikan oleh Bendahara)',
        'disetujui' => 'Siap Dicairkan (Disetujui Bendahara)',
        'dicairkan' => 'Selesai & Lunas Dicairkan',
        default => $record->status_label,
    };
@endphp

<div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-900">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3 dark:border-white/5">
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ str_contains($status, 'revisi') ? 'bg-red-400' : ($status === 'dicairkan' ? 'bg-green-400' : 'bg-amber-400') }} opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 {{ str_contains($status, 'revisi') ? 'bg-red-500' : ($status === 'dicairkan' ? 'bg-green-500' : 'bg-amber-500') }}"></span>
            </span>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Posisi Berkas Saat Ini:
                </span>
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">
                    {{ $heroTitle }}
                </h3>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="rounded-full px-2.5 py-1 text-xs font-bold
                {{ match ($record->status_color) {
                    'success' => 'bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300',
                    'danger' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300',
                    'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                    'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                } }}">
                {{ $record->status_label }}
            </span>
        </div>
    </div>

    {{-- Stepper Progress Bar --}}
    <div class="pt-4 overflow-x-auto">
        <div class="flex items-center justify-between min-w-[550px] gap-2">
            @foreach ($steps as $num => $step)
                @php
                    $state = $stepStates[$num];
                @endphp
                <div class="flex-1 flex flex-col items-center text-center relative">
                    {{-- Bulatan Step --}}
                    <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition shadow-xs z-10
                        {{ match ($state) {
                            'completed' => 'bg-green-500 text-white ring-4 ring-green-100 dark:ring-green-950/60',
                            'active' => 'bg-amber-500 text-white ring-4 ring-amber-100 dark:ring-amber-950/60 animate-pulse',
                            'revision' => 'bg-red-500 text-white ring-4 ring-red-100 dark:ring-red-950/60',
                            default => 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                        } }}">
                        @if ($state === 'completed')
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @elseif ($state === 'revision')
                            <span>!</span>
                        @else
                            <span>{{ $num }}</span>
                        @endif
                    </div>

                    <span class="mt-1.5 text-[11px] font-bold {{ $state === 'pending' ? 'text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-gray-100' }}">
                        {{ $step['label'] }}
                    </span>

                    @if ($step['actor'])
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate max-w-[90px] block" title="{{ $step['actor'] }}">
                            {{ $step['actor'] }}
                        </span>
                    @endif
                </div>

                @if ($num < 6)
                    <div class="flex-1 h-0.5 -mt-5 {{ $stepStates[$num + 1] !== 'pending' ? 'bg-green-500 dark:bg-green-600' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endif
            @endforeach
        </div>
    </div>
</div>
