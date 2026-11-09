<?php

namespace Tests\Feature;

use App\Filament\Resources\MasterSyaratBelanjas\Pages\ListMasterSyaratBelanjas;
use App\Models\MasterSyaratBelanja;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class MasterSyaratTest extends TestCase
{
    public function test_full_lifecycle_of_master_syarat_belanja(): void
    {
        $user = User::role('super_admin')->first();
        $this->actingAs($user);

        // 1. Mount test
        $test = Livewire::test(ListMasterSyaratBelanjas::class);
        $test->assertSuccessful();

        $slug = 'pemeliharaan_ac_unit';

        // 2. Test Tambah Syarat Baru via CreateAction
        $test->callAction('create', [
            'metode_pengadaan' => 'non_katalog',
            'jenis_belanja' => $slug,
            'nama_syarat' => 'Nota Servis AC Resmi',
            'keterangan' => 'Pengadaan jasa perawatan AC berkala',
            'is_required' => true,
            'aktif' => true,
        ]);
        $test->assertHasNoActionErrors();

        $item1 = MasterSyaratBelanja::where('jenis_belanja', $slug)->first();
        $this->assertNotNull($item1, 'Syarat baru harus berhasil tersimpan di DB');
        $this->assertEquals('Nota Servis AC Resmi', $item1->nama_syarat);
        $this->assertEquals(1, $item1->urutan, 'Urutan pertama otomatis terisi 1');

        // 3. Test Tambah Syarat Kedua pada pekerjaan yang sama (urutan otomatis jadi 2)
        $test->callAction('create', [
            'metode_pengadaan' => 'non_katalog',
            'jenis_belanja' => $slug,
            'nama_syarat' => 'Foto Dokumentasi Unit AC',
            'keterangan' => 'Foto AC sebelum dan setelah dibersihkan',
            'is_required' => true,
            'aktif' => true,
        ]);
        $test->assertHasNoActionErrors();

        $item2 = MasterSyaratBelanja::where('jenis_belanja', $slug)
            ->where('nama_syarat', 'Foto Dokumentasi Unit AC')
            ->first();
        $this->assertNotNull($item2, 'Syarat kedua harus berhasil dibuat');
        $this->assertEquals(2, $item2->urutan, 'Urutan kedua otomatis terisi 2');

        // 4. Test Edit Syarat via Table Record Action (EditAction)
        $test->callTableAction('edit', $item2, [
            'nama_syarat' => 'Foto Unit AC dan Faktur Toko',
            'keterangan' => 'Keterangan diperbarui lengkap',
            'is_required' => false,
            'aktif' => true,
        ]);
        $test->assertHasNoActionErrors();

        $item2->refresh();
        $this->assertEquals('Foto Unit AC dan Faktur Toko', $item2->nama_syarat);
        $this->assertFalse((bool) $item2->is_required);

        // 5. Test Swap / Reorder
        $this->assertEquals(1, $item1->fresh()->urutan);
        $this->assertEquals(2, $item2->fresh()->urutan);

        $test->call('swapUrutan', $item2->id, 'up');

        $this->assertEquals(2, $item1->fresh()->urutan, 'Item 1 harus bergeser ke urutan 2');
        $this->assertEquals(1, $item2->fresh()->urutan, 'Item 2 harus bergeser naik ke urutan 1');

        // 6. Test Hapus Syarat via Table Record Action (DeleteAction)
        $test->callTableAction('delete', $item2);
        $test->assertHasNoActionErrors();

        $this->assertNull(MasterSyaratBelanja::find($item2->id), 'Syarat harus terhapus dari DB');

        // Bersihkan sisa
        $item1->delete();
    }
}
