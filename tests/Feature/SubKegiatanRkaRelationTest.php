<?php

namespace Tests\Feature;

use App\Filament\Resources\MasterSubKegiatans\Pages\EditMasterSubKegiatan;
use App\Filament\Resources\MasterSubKegiatans\Pages\ListMasterSubKegiatans;
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

    public function test_list_sub_kegiatan_renders_correctly(): void
    {
        $user = User::role('super_admin')->first();
        $this->actingAs($user);

        $testList = Livewire::test(ListMasterSubKegiatans::class);
        $testList->assertSuccessful();
    }

    public function test_pptk_and_jf_role_scoping_and_permissions(): void
    {
        $subKegiatan1 = MasterSubKegiatan::with('kegiatan.bidang')->first();
        $this->assertNotNull($subKegiatan1);
        $bidangId = $subKegiatan1->kegiatan?->bidang_id;
        $this->assertNotNull($bidangId);

        $pptk = User::role('pptk')->where('bidang_id', $bidangId)->first();
        if (! $pptk) {
            $pptk = User::factory()->create(['bidang_id' => $bidangId, 'aktif' => true]);
            $pptk->assignRole('pptk');
        }

        $jf = User::role('jf')->where('bidang_id', $bidangId)->first();
        if (! $jf) {
            $jf = User::factory()->create([
                'bidang_id' => $bidangId,
                'aktif' => true,
            ]);
            $jf->assignRole('jf');
        }

        $otherJf = User::factory()->create([
            'bidang_id' => $bidangId,
            'aktif' => true,
        ]);
        $otherJf->assignRole('jf');

        $subKegiatan1->update(['jf_id' => $jf->id]);

        $subKegiatan2 = MasterSubKegiatan::whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $bidangId))
            ->where('id', '!=', $subKegiatan1->id)
            ->first();

        if ($subKegiatan2) {
            $subKegiatan2->update(['jf_id' => $otherJf->id]);
        }

        // 1. Verifikasi PPTK: Bisa melihat seluruh Sub Kegiatan di bidangnya
        $this->actingAs($pptk);
        $pptkQuery = \App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource::getEloquentQuery();
        $this->assertTrue($pptkQuery->where('id', $subKegiatan1->id)->exists());
        if ($subKegiatan2) {
            $this->assertTrue($pptkQuery->where('id', $subKegiatan2->id)->exists());
        }
        $this->assertTrue($pptk->can('create', MasterSubKegiatan::class));

        // 2. Verifikasi JF: HANYA bisa melihat Sub Kegiatan miliknya sendiri
        $this->actingAs($jf);
        $jfQuery = \App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource::getEloquentQuery();
        $this->assertTrue($jfQuery->where('id', $subKegiatan1->id)->exists());
        if ($subKegiatan2) {
            $this->assertFalse($jfQuery->where('id', $subKegiatan2->id)->exists());
        }

        // 3. Verifikasi Policy JF: Tidak bisa membuat atau menghapus Sub Kegiatan
        $this->assertFalse($jf->can('create', MasterSubKegiatan::class));
        $this->assertFalse($jf->can('delete', $subKegiatan1));
        $this->assertTrue($jf->can('view', $subKegiatan1));
        $this->assertTrue($jf->can('update', $subKegiatan1));

        // 4. Bersihkan data test user tambahan
        $otherJf->delete();
    }
}
