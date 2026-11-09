<?php

namespace Tests\Feature;

use App\Filament\Resources\MasterJenisBelanjas\Pages\EditMasterJenisBelanja;
use App\Filament\Resources\MasterJenisBelanjas\Pages\ListMasterJenisBelanjas;
use App\Filament\Resources\MasterJenisBelanjas\RelationManagers\SyaratDokumenRelationManager;
use App\Models\MasterJenisBelanja;
use App\Models\MasterSyaratBelanja;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class MasterSyaratTest extends TestCase
{
    public function test_full_lifecycle_of_master_jenis_belanja_and_syarat(): void
    {
        $user = User::role('super_admin')->first();
        $this->actingAs($user);

        // 1. Mount List Page Master Jenis Belanja
        $testList = Livewire::test(ListMasterJenisBelanjas::class);
        $testList->assertSuccessful();

        // 2. Buat Jenis Pekerjaan Baru via MasterJenisBelanja
        $job = MasterJenisBelanja::create([
            'nama' => 'Pemeliharaan AC Kantor Unit',
            'metode_pengadaan' => 'non_katalog',
            'deskripsi' => 'Pengadaan jasa perawatan berkala AC dinas',
            'aktif' => true,
        ]);

        $this->assertNotNull($job->id);
        $this->assertEquals('pemeliharaan_ac_kantor_unit', $job->kode);

        // 3. Mount Edit Page dengan Relation Manager Syarat Dokumen
        $testEdit = Livewire::test(EditMasterJenisBelanja::class, [
            'record' => $job->id,
        ]);
        $testEdit->assertSuccessful();

        // 4. Test Tambah Syarat Dokumen via Relation Manager
        $testRel = Livewire::test(SyaratDokumenRelationManager::class, [
            'ownerRecord' => $job,
            'pageClass' => EditMasterJenisBelanja::class,
        ]);
        $testRel->assertSuccessful();

        $testRel->callTableAction('create', null, [
            'nama_syarat' => 'Nota Servis AC Resmi',
            'keterangan' => 'Nota berstempel dari bengkel pendingin resmi',
            'is_required' => true,
            'aktif' => true,
        ]);
        $testRel->assertHasNoTableActionErrors();

        $item1 = MasterSyaratBelanja::where('master_jenis_belanja_id', $job->id)
            ->where('nama_syarat', 'Nota Servis AC Resmi')
            ->first();

        $this->assertNotNull($item1, 'Syarat dokumen harus berhasil tersimpan');
        $this->assertEquals(1, $item1->urutan);
        $this->assertEquals($job->kode, $item1->jenis_belanja);
        $this->assertEquals($job->metode_pengadaan, $item1->metode_pengadaan);

        // 5. Tambah Dokumen Kedua via Relation Manager
        $testRel->callTableAction('create', null, [
            'nama_syarat' => 'Foto Dokumentasi Sebelum dan Sesudah',
            'keterangan' => 'Foto fisik pengerjaan AC',
            'is_required' => true,
            'aktif' => true,
        ]);
        $testRel->assertHasNoTableActionErrors();

        $item2 = MasterSyaratBelanja::where('master_jenis_belanja_id', $job->id)
            ->where('nama_syarat', 'Foto Dokumentasi Sebelum dan Sesudah')
            ->first();

        $this->assertNotNull($item2);
        $this->assertEquals(2, $item2->urutan);

        // 6. Test Edit Dokumen via Table Action
        $testRel->callTableAction('edit', $item2, [
            'nama_syarat' => 'Foto Fisik Unit AC Diperbaiki',
            'keterangan' => 'Keterangan diperbarui',
            'is_required' => false,
            'aktif' => true,
        ]);
        $testRel->assertHasNoTableActionErrors();

        $item2->refresh();
        $this->assertEquals('Foto Fisik Unit AC Diperbaiki', $item2->nama_syarat);
        $this->assertFalse((bool) $item2->is_required);

        // 7. Test Hapus Dokumen via Table Action
        $testRel->callTableAction('delete', $item2);
        $testRel->assertHasNoTableActionErrors();

        $this->assertNull(MasterSyaratBelanja::find($item2->id));

        // 8. Bersihkan data test
        $item1->delete();
        $job->delete();
        $this->assertNull(MasterJenisBelanja::find($job->id));
    }
}
