@php
    use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
    use App\Support\FormatHelper;

    $subKegiatans = $kegiatan->subKegiatans()
        ->with(['jf', 'rincianAnggarans'])
        ->get();
    
    $totalPagu = $subKegiatans->sum('total_pagu');
@endphp

<div class="space-y-5 text-sm text-gray-700 dark:text-gray-200">
    {{-- Kegiatan Summary Card --}}
    <div class="p-4 rounded-xl border border-primary-200 bg-primary-50/50 dark:border-primary-900/50 dark:bg-primary-950/20">
        <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="px-2 py-0.5 text-xs font-semibold rounded-md bg-primary-600 text-white">
                Tahun {{ $kegiatan->tahun_anggaran }}
            </span>
            <span class="text-xs font-mono font-bold text-primary-700 dark:text-primary-300">
                [{{ $kegiatan->kode_kegiatan }}]
            </span>
            @if ($kegiatan->bidang)
                <span class="px-2 py-0.5 text-xs rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium">
                    {{ $kegiatan->bidang->nama }}
                </span>
            @endif
        </div>
        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 uppercase tracking-tight">
            {{ $kegiatan->nama_kegiatan }}
        </h3>
        @if ($kegiatan->program)
            <div class="mt-1 text-xs text-slate-600 dark:text-slate-400 font-semibold uppercase">
                Program: [{{ $kegiatan->program->kode_program }}] {{ $kegiatan->program->nama_program }}
            </div>
        @endif

        <div class="mt-3 pt-3 border-t border-primary-200/60 dark:border-primary-800/60 grid grid-cols-2 gap-2 text-center text-xs">
            <div>
                <span class="block text-gray-500 dark:text-gray-400">Total Sub Kegiatan</span>
                <span class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $subKegiatans->count() }} Sub</span>
            </div>
            <div>
                <span class="block text-gray-500 dark:text-gray-400">Total Pagu RKA</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ FormatHelper::rupiah($totalPagu) }}</span>
            </div>
        </div>
    </div>

    {{-- Daftar Sub Kegiatan --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm flex items-center gap-2">
                <x-heroicon-m-document-text class="w-4 h-4 text-primary-600" />
                Daftar Sub Kegiatan ({{ $subKegiatans->count() }})
            </h4>
            <a href="{{ MasterSubKegiatanResource::getUrl('index', ['tableFilters' => ['kegiatan_id' => ['value' => $kegiatan->id]]]) }}"
               target="_blank"
               class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:underline inline-flex items-center gap-1">
                Buka di Halaman Sub Kegiatan
                <x-heroicon-m-arrow-top-right-on-square class="w-3 h-3" />
            </a>
        </div>

        @if ($subKegiatans->count() > 0)
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100/60 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-2.5 px-3 font-semibold">Sub Kegiatan</th>
                                <th class="py-2.5 px-3 font-semibold">Penanggung Jawab (JF)</th>
                                <th class="py-2.5 px-3 font-semibold text-right">Pagu RKA</th>
                                <th class="py-2.5 px-3 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($subKegiatans as $sub)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                    <td class="py-2.5 px-3">
                                        <div class="font-normal text-gray-900 dark:text-gray-100 lowercase">
                                            @if ($sub->kode_sub_kegiatan)
                                                <span class="font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">[{{ $sub->kode_sub_kegiatan }}]</span>
                                            @endif
                                            {{ $sub->nama_sub_kegiatan }}
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if ($sub->jf)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                <x-heroicon-m-check-circle class="w-3 h-3" />
                                                {{ $sub->jf->name }}
                                            </span>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Belum Ditugaskan</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                        {{ FormatHelper::rupiah($sub->total_pagu) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <a href="{{ MasterSubKegiatanResource::getUrl('edit', ['record' => $sub]) }}"
                                           class="inline-flex items-center gap-1 text-[11px] font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:underline">
                                            <x-heroicon-m-pencil class="w-3 h-3" />
                                            Ubah
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                Belum ada sub kegiatan yang terdaftar di bawah kegiatan ini.
            </div>
        @endif
    </div>
</div>

