<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan seluruh role sistem terdaftar
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        $paRole = Role::firstOrCreate(['name' => 'pa', 'guard_name' => 'web']);
        $ppkRole = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $pptkRole = Role::firstOrCreate(['name' => 'pptk', 'guard_name' => 'web']);
        $jfRole = Role::firstOrCreate(['name' => 'jf', 'guard_name' => 'web']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);

        // Hapus role usang yang tidak lagi dipakai
        Role::query()->whereIn('name', ['staf_keuangan', 'jf_staf'])->delete();

        // Buat permissions widget jika belum ada agar seeder tidak error karena data kosong
        Permission::firstOrCreate(['name' => 'View:PegawaiAbsensiBulanChartWidget', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'View:PegawaiAbsensiBulanStatsWidget', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'View:KeuanganBidangOverviewWidget', 'guard_name' => 'web']);

        // === 0. Setup Role Super Admin ===
        $superAdminRole->syncPermissions(Permission::all());

        // === 1. Setup Role Admin Dinas ===
        $adminPermissions = Permission::where('name', 'not like', '%Role%')->get();
        $adminRole->syncPermissions($adminPermissions);

        // Permissions Dasar Kepegawaian (Presensi, LHK, Profil Diri)
        $basicPegawaiPermissions = [
            'ViewAny:AbsensiHarian',
            'View:AbsensiHarian',
            'ViewAny:LogAbsensi',
            'View:LogAbsensi',
            'ViewAny:Lhk',
            'View:Lhk',
            'Create:Lhk',
            'Update:Lhk',
            'Delete:Lhk',
            'View:User',
            'Update:User',
            'View:Dashboard',
            'View:KeuanganBidangOverviewWidget',
            'View:LatestAbsensiWidget',
            'View:PegawaiAbsensiBulanChartWidget',
            'View:PegawaiAbsensiBulanStatsWidget',
        ];

        // === 2. Setup Role PA (Pengguna Anggaran / Kepala Dinas) ===
        // Otorisasi tertinggi anggaran se-Dinas (semua bidang), persetujuan & TTD Kwitansi + NPD, pantau penyerapan pagu
        $paPermissions = Permission::whereIn('name', array_merge($basicPegawaiPermissions, [
            'ViewAny:PengajuanSpj',
            'View:PengajuanSpj',
            'Update:PengajuanSpj',
            'ViewAny:RincianAnggaranBelanja',
            'View:RincianAnggaranBelanja',
            'ViewAny:MasterProgram',
            'View:MasterProgram',
            'ViewAny:MasterKegiatan',
            'View:MasterKegiatan',
            'ViewAny:MasterSubKegiatan',
            'View:MasterSubKegiatan',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
        ]))->get();
        $paRole->syncPermissions($paPermissions);

        // === 3. Setup Role PPK (Pejabat Pembuat Komitmen / Kasubag Keuangan / Sekdis) ===
        // Verifikasi administrasi & pagu se-Dinas (checklist kelayakan), kelola DPA/RKA & kuota belanja RKA
        $ppkPermissions = Permission::whereIn('name', array_merge($basicPegawaiPermissions, [
            'ViewAny:PengajuanSpj',
            'View:PengajuanSpj',
            'Update:PengajuanSpj',
            'ViewAny:RincianAnggaranBelanja',
            'View:RincianAnggaranBelanja',
            'Create:RincianAnggaranBelanja',
            'Update:RincianAnggaranBelanja',
            'ViewAny:MasterProgram',
            'View:MasterProgram',
            'Create:MasterProgram',
            'Update:MasterProgram',
            'ViewAny:MasterKegiatan',
            'View:MasterKegiatan',
            'Create:MasterKegiatan',
            'Update:MasterKegiatan',
            'ViewAny:MasterSubKegiatan',
            'View:MasterSubKegiatan',
            'Create:MasterSubKegiatan',
            'Update:MasterSubKegiatan',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'Create:KodeRekening',
            'Update:KodeRekening',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'Create:MasterToko',
            'Update:MasterToko',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
            'Create:MasterBarang',
            'Update:MasterBarang',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
        ]))->get();
        $ppkRole->syncPermissions($ppkPermissions);

        // === 4. Setup Role PPTK (Pejabat Pelaksana Teknis Kegiatan / Kepala Bidang) ===
        // Otorisasi teknis kegiatan di bidangnya: TTD Surat Pesanan (Tahap 1), Verifikasi & TTD Kwitansi (Tahap 2)
        $pptkPermissions = Permission::whereIn('name', array_merge($basicPegawaiPermissions, [
            'ViewAny:PengajuanSpj',
            'View:PengajuanSpj',
            'Create:PengajuanSpj',
            'Update:PengajuanSpj',
            'ViewAny:RincianAnggaranBelanja',
            'View:RincianAnggaranBelanja',
            'ViewAny:MasterKegiatan',
            'View:MasterKegiatan',
            'ViewAny:MasterSubKegiatan',
            'View:MasterSubKegiatan',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
        ]))->get();
        $pptkRole->syncPermissions($pptkPermissions);

        // === 5. Setup Role JF (Jabatan Fungsional / Pengusul SPJ Bidang) ===
        // Pengusul belanja: buat draf Surat Pesanan, input realisasi aktual, unggah faktur/foto fisik toko
        $jfPermissions = Permission::whereIn('name', array_merge($basicPegawaiPermissions, [
            'ViewAny:PengajuanSpj',
            'View:PengajuanSpj',
            'Create:PengajuanSpj',
            'Update:PengajuanSpj',
            'Delete:PengajuanSpj',
            'ViewAny:RincianAnggaranBelanja',
            'View:RincianAnggaranBelanja',
            'ViewAny:MasterKegiatan',
            'View:MasterKegiatan',
            'ViewAny:MasterSubKegiatan',
            'View:MasterSubKegiatan',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'Create:MasterToko',
            'Update:MasterToko',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
        ]))->get();
        $jfRole->syncPermissions($jfPermissions);

        // === 6. Setup Role Bendahara Pengeluaran ===
        // Uji kas, pencairan dana, pemungutan pajak, input nomor BKU & tgl cair, serta TTD Lunas Kwitansi
        $bendaharaPermissions = Permission::whereIn('name', array_merge($basicPegawaiPermissions, [
            'ViewAny:PengajuanSpj',
            'View:PengajuanSpj',
            'Update:PengajuanSpj',
            'ViewAny:RincianAnggaranBelanja',
            'View:RincianAnggaranBelanja',
            'ViewAny:MasterProgram',
            'View:MasterProgram',
            'ViewAny:MasterKegiatan',
            'View:MasterKegiatan',
            'ViewAny:MasterSubKegiatan',
            'View:MasterSubKegiatan',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
        ]))->get();
        $bendaharaRole->syncPermissions($bendaharaPermissions);

        // === 7. Setup Role Pegawai Standar (Non-Keuangan Khusus) ===
        $pegawaiPermissions = Permission::whereIn('name', array_merge($basicPegawaiPermissions, [
            'ViewAny:PengajuanSpj',
            'View:PengajuanSpj',
            'Create:PengajuanSpj',
            'Update:PengajuanSpj',
            'Delete:PengajuanSpj',
            'ViewAny:MasterKegiatan',
            'View:MasterKegiatan',
            'ViewAny:MasterSubKegiatan',
            'View:MasterSubKegiatan',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'Create:MasterToko',
            'Update:MasterToko',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
        ]))->get();
        $pegawaiRole->syncPermissions($pegawaiPermissions);

        $this->command?->info('Hak Akses (Permissions) untuk Role Keuangan (PA, PPK, PPTK, Staf Keuangan, Bendahara, Admin, Pegawai) telah berhasil disinkronisasi.');
    }
}
