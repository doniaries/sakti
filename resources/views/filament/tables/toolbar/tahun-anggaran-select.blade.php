<div class="inline-flex items-center gap-1.5 rounded-lg border border-primary-500/30 bg-primary-500/10 px-3 py-1.5 text-xs font-semibold text-primary-600 shadow-sm dark:border-primary-400/30 dark:bg-primary-950/40 dark:text-primary-300 select-none" wire:key="toolbar-keterangan-tahun">
    <x-heroicon-m-calendar-days class="h-4 w-4 text-primary-600 dark:text-primary-400" />
    <span>Tahun Anggaran: <strong class="font-bold text-primary-700 dark:text-primary-200">{{ \App\Models\PengaturanAplikasi::getTahunAnggaranAktif() }}</strong></span>
</div>
