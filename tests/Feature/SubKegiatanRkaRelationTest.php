<?php

namespace Tests\Feature;

use App\Filament\Resources\MasterSubKegiatans\Pages\EditMasterSubKegiatan;
use App\Filament\Resources\MasterSubKegiatans\RelationManagers\RincianAnggaranBelanjaRelationManager;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use App\Models\Satuan;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class SubKegiatanRkaRelationTest extends TestCase
{
    public function test_sub_kegiatan_rka_relation_manager_lifecycle(): void
    {
        $user = User::role('super_admin')->first();
        $this->actingAs($user);

        $subKegiatan = MasterSubKegiatan::first();
        $this->assertNotNull($subKegiatan);

        // 1. Mount Edit Page Sub Kegiatan
        $testEdit = Livewire::test(EditMasterSubKegiatan::class, [
            'record' => $subKegiatan->id,
        ]);
        $testEdit->assertSuccessful();

        // 2. Mount Relation Manager RKA
        $testRel = Livewire::test(RincianAnggaranBelanjaRelationManager::class, [
            'ownerRecord' => $subKegiatan,
            'pageClass' => EditMasterSubKegiatan::class,
        ]);
        $testRel->assertSuccessful();

        // 3. Persiapkan Rekening & Barang
        $rek = KodeRekening::hanyaSubRincianObyek()->first();
        $satuan = Satuan::firstOrCreate(['nama' => 'Kotak']);
        $barang = MasterBarang::firstOrCreate([
            'nama_barang' => 'Kertas Bufalo Folio Warna',
        ], [
            'kode_rekening_id' => $rek->id,
            'satuan_id' => $satuan->id,
            'harga_satuan' => 45000,
        ]);

        // 4. Test Tambah Item Belanja RKA
        $testRel->callTableAction('create', null, [
            'sumber_dana' => \App\Enums\SumberDana::DAU->value,
            'kode_rekening_id' => $rek->id,
            'master_barang_id' => $barang->id,
            'nama_item' => 'Kertas Bufalo Folio Warna',
            'spesifikasi' => 'Warna Biru Muda, 160 GSM',
            'volume' => 10,
            'satuan' => 'Kotak',
            'harga_satuan' => 45000,
        ]);
        $testRel->assertHasNoTableActionErrors();

        $item = RincianAnggaranBelanja::where('sub_kegiatan_id', $subKegiatan->id)
            ->where('nama_item', 'Kertas Bufalo Folio Warna')
            ->first();

        $this->assertNotNull($item);
        $this->assertEquals(450000, (float) $item->pagu_total);
        $this->assertEquals($subKegiatan->id, $item->sub_kegiatan_id);
        $this->assertEquals($subKegiatan->tahun_anggaran, $item->tahun_anggaran);

        // 5. Bersihkan data test
        $item->delete();
    }
}
