<x-filament-panels::page>
    <x-filament::section
        heading="Daftar Berkas Backup SQL"
        description="Simpan dan unduh salinan database secara berkala agar data dapat dipulihkan kapan saja.">
        @if ($backups === [])
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada berkas backup tersimpan. Klik <strong>Buat Backup SQL</strong> di kanan atas untuk membuat salinan pertama.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-white/10">
                    <thead>
                        <tr class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="py-3 pr-4">Nama Berkas</th>
                            <th class="py-3 px-4">Tanggal Dibuat</th>
                            <th class="py-3 px-4">Ukuran</th>
                            <th class="py-3 pl-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($backups as $backup)
                            <tr wire:key="backup-{{ $backup['filename'] }}">
                                <td class="py-3 pr-4 font-medium">{{ $backup['filename'] }}</td>
                                <td class="py-3 px-4">{{ $backup['created_at'] }}</td>
                                <td class="py-3 px-4">{{ $backup['size_human'] }}</td>
                                <td class="py-3 pl-4 text-right space-x-2">
                                    <x-filament::button
                                        size="xs"
                                        color="success"
                                        icon="heroicon-o-arrow-down-tray"
                                        wire:click="downloadFile('{{ $backup['filename'] }}')">
                                        Unduh
                                    </x-filament::button>

                                    <x-filament::button
                                        size="xs"
                                        color="warning"
                                        icon="heroicon-o-arrow-path"
                                        wire:click="restoreFile('{{ $backup['filename'] }}')"
                                        wire:confirm="Yakin ingin me-restore database dari {{ $backup['filename'] }}? Data saat ini akan ditimpa.">
                                        Restore
                                    </x-filament::button>

                                    <x-filament::button
                                        size="xs"
                                        color="danger"
                                        icon="heroicon-o-trash"
                                        wire:click="deleteFile('{{ $backup['filename'] }}')"
                                        wire:confirm="Hapus berkas backup {{ $backup['filename'] }}?">
                                        Hapus
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>

