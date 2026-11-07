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

        // 2. Test Tambah Pekerjaan Baru
        $test->callAction('tambahPekerjaan', [
            'metode_pengadaan' => 'non_katalog',
            'nama_pekerjaan' => 'Pemeliharaan AC Kantor Unit',
            'syarat_pertama' => 'Nota Servis AC Resmi',
            'deskripsi' => 'Pengadaan jasa perawatan AC berkala',
        ]);
        $test->assertHasNoActionErrors();

        $slug = 'pemeliharaan_ac_kantor_unit';
        $item1 = MasterSyaratBelanja::where('jenis_belanja', $slug)->first();
        $this->assertNotNull($item1, 'Pekerjaan baru harus berhasil tersimpan di DB');
        $this->assertEquals('Nota Servis AC Resmi', $item1->nama_syarat);
        $this->assertEquals(1, $item1->urutan);

        // 3. Test Tambah Syarat Kedua pada pekerjaan tersebut via tambahSyarat
        $test->callAction('tambahSyarat', [
            'jenis_belanja' => $slug,
            'metode_pengadaan' => 'non_katalog',
            'nama_syarat' => 'Foto Dokumentasi Unit AC',
            'keterangan' => 'Foto AC sebelum dan setelah dibersihkan teknisi',
            'is_required' => true,
            'aktif' => true,
        ]);
        $test->assertHasNoActionErrors();

        $item2 = MasterSyaratBelanja::where('jenis_belanja', $slug)
            ->where('nama_syarat', 'Foto Dokumentasi Unit AC')
            ->first();
        $this->assertNotNull($item2, 'Syarat kedua harus berhasil dibuat');
        $this->assertEquals(2, $item2->urutan);

        // 4. Test Edit Syarat via editSyarat
        $test->callAction('editSyarat', [
            'id' => $item2->id,
            'nama_syarat' => 'Foto Unit AC dan Faktur Toko',
            'keterangan' => 'Keterangan diperbarui lengkap',
            'is_required' => false,
            'aktif' => true,
        ]);
        $test->assertHasNoActionErrors();

        $item2->refresh();
        $this->assertEquals('Foto Unit AC dan Faktur Toko', $item2->nama_syarat);
        $this->assertFalse((bool) $item2->is_required);

        // 5. Test Swap Urutan (Naik)
        $this->assertEquals(1, $item1->fresh()->urutan);
        $this->assertEquals(2, $item2->fresh()->urutan);

        $test->call('swapUrutan', $item2->id, 'up');

        $this->assertEquals(2, $item1->fresh()->urutan, 'Item 1 harus bergeser ke urutan 2');
        $this->assertEquals(1, $item2->fresh()->urutan, 'Item 2 harus bergeser naik ke urutan 1');

        // 6. Test Hapus Syarat via hapusSyarat
        $test = Livewire::test(ListMasterSyaratBelanjas::class);
        $test->callAction('hapusSyarat', [
            'id' => $item2->id,
        ]);
        $test->assertHasNoActionErrors();

        $this->assertNull(MasterSyaratBelanja::find($item2->id), 'Syarat harus terhapus dari DB');

        // 7. Test Hapus Pekerjaan via hapusPekerjaan
        $test = Livewire::test(ListMasterSyaratBelanjas::class);
        $test->callAction('hapusPekerjaan', [
            'jenis_belanja' => $slug,
            'metode_pengadaan' => 'non_katalog',
            'label' => 'Pemeliharaan AC Kantor Unit',
        ]);
        $test->assertHasNoActionErrors();

        $remainingCount = MasterSyaratBelanja::where('jenis_belanja', $slug)->count();
        $this->assertEquals(0, $remainingCount, 'Seluruh syarat pada pekerjaan harus terhapus');
    }
}
