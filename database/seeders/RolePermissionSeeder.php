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
        $adminKepegawaianRole = Role::firstOrCreate(['name' => 'admin_kepegawaian', 'guard_name' => 'web']);
        $adminKeuanganRole = Role::firstOrCreate(['name' => 'admin_keuangan', 'guard_name' => 'web']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        $paRole = Role::firstOrCreate(['name' => 'pa', 'guard_name' => 'web']);
        $ppkRole = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $pptkRole = Role::firstOrCreate(['name' => 'pptk', 'guard_name' => 'web']);
        $jfRole = Role::firstOrCreate(['name' => 'jf', 'guard_name' => 'web']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);

        // Hapus role usang yang tidak lagi dipakai
        Role::query()->whereIn('name', ['staf_keuangan', 'jf_staf'])->delete();

        // Buat permissions dasar & page/widget jika belum ada di server agar seeder selalu idempoten
        foreach ([
            'View:PegawaiAbsensiBulanChartWidget',
            'View:PegawaiAbsensiBulanStatsWidget',
            'View:KeuanganBidangOverviewWidget',
            'View:LatestAbsensiWidget',
            'View:Dashboard',
            'View:EditProfilePage',
            'View:LaporanFungsional',
            'View:LaporanLrfk',
        ] as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        foreach (['PengajuanIzin', 'HariLibur', 'Lhk', 'RencanaAksi', 'Aktivitas', 'Reputasi', 'AbsensiHarian', 'LogAbsensi', 'User'] as $res) {
            foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $act) {
                Permission::firstOrCreate(['name' => "{$act}:{$res}", 'guard_name' => 'web']);
            }
        }

        // === 0. Setup Role Super Admin ===
        $superAdminRole->syncPermissions(Permission::all());

        // === 1. Setup Role Admin Dinas ===
        $adminPermissions = Permission::where('name', 'not like', '%Role%')->get();
        $adminRole->syncPermissions($adminPermissions);

        // Permissions Dasar Kepegawaian (Presensi, LHK, Izin/Cuti, Profil Diri)
        $basicPegawaiPermissions = [
            'ViewAny:AbsensiHarian',
            'View:AbsensiHarian',
            'ViewAny:LogAbsensi',
            'View:LogAbsensi',
            'ViewAny:PengajuanIzin',
            'View:PengajuanIzin',
            'Create:PengajuanIzin',
            'Update:PengajuanIzin',
            'Delete:PengajuanIzin',
            'ViewAny:HariLibur',
            'View:HariLibur',
            'ViewAny:Lhk',
            'View:Lhk',
            'Create:Lhk',
            'Update:Lhk',
            'Delete:Lhk',
            'ViewAny:RencanaAksi',
            'View:RencanaAksi',
            'Create:RencanaAksi',
            'Update:RencanaAksi',
            'Delete:RencanaAksi',
            'ViewAny:Aktivitas',
            'View:Aktivitas',
            'Create:Aktivitas',
            'Update:Aktivitas',
            'Delete:Aktivitas',
            'ViewAny:Reputasi',
            'View:Reputasi',
            'Create:Reputasi',
            'Update:Reputasi',
            'View:User',
            'Update:User',
            'View:Dashboard',
            'View:EditProfilePage',
            'View:KeuanganBidangOverviewWidget',
            'View:LatestAbsensiWidget',
            'View:PegawaiAbsensiBulanChartWidget',
            'View:PegawaiAbsensiBulanStatsWidget',
        ];

        // === 1A. Setup Role Admin Kepegawaian ===
        // Khusus mengelola seluruh data kepegawaian (Pegawai, Bidang, Jabatan, Shift, Absensi, Izin/Cuti, LHK)
        $kepegawaianPatterns = ['User', 'Bidang', 'Jabatan', 'JadwalKerja', 'JenisIzin', 'PengajuanIzin', 'HariLibur', 'MesinAbsensi', 'LogAbsensi', 'AbsensiHarian', 'Lhk', 'RencanaAksi', 'Aktivitas', 'Reputasi', 'Riwayat', 'Instansi', 'PengaturanAplikasi', 'PegawaiAbsensi'];
        $adminKepegawaianPermissions = Permission::where(function ($q) use ($kepegawaianPatterns, $basicPegawaiPermissions) {
            foreach ($kepegawaianPatterns as $pattern) {
                $q->orWhere('name', 'like', "%:{$pattern}%");
            }
            $q->orWhereIn('name', $basicPegawaiPermissions);
        })->where('name', 'not like', '%Role%')->get();
        $adminKepegawaianRole->syncPermissions($adminKepegawaianPermissions);

        // === 1B. Setup Role Admin Keuangan ===
        // Khusus mengelola seluruh master data keuangan & input DPA/RKA/SPJ lintas seluruh bidang
        $keuanganPatterns = ['Program', 'Kegiatan', 'SubKegiatan', 'RincianAnggaranBelanja', 'KodeRekening', 'MasterBarang', 'MasterJenisBelanja', 'MasterToko', 'MasterSyaratBelanja', 'MasterTujuanBbm', 'PengajuanSpj', 'StokBarang', 'LaporanPersediaan', 'LaporanFungsional', 'LaporanLrfk', 'TransaksiBarang', 'KeuanganBidangOverviewWidget'];
        $adminKeuanganPermissions = Permission::where(function ($q) use ($keuanganPatterns, $basicPegawaiPermissions) {
            foreach ($keuanganPatterns as $pattern) {
                $q->orWhere('name', 'like', "%:{$pattern}%");
            }
            $q->orWhereIn('name', array_merge($basicPegawaiPermissions, ['ViewAny:Bidang', 'View:Bidang', 'ViewAny:User', 'View:User']));
        })->where('name', 'not like', '%Role%')->get();
        $adminKeuanganRole->syncPermissions($adminKeuanganPermissions);

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
            'ViewAny:MasterBarang',
            'View:MasterBarang',
            'ViewAny:MasterSyaratBelanja',
            'View:MasterSyaratBelanja',
            'ViewAny:MasterTujuanBbm',
            'View:MasterTujuanBbm',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
            'View:LaporanFungsional',
            'View:LaporanLrfk',
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
            'Delete:MasterSubKegiatan',
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
            'ViewAny:MasterSyaratBelanja',
            'View:MasterSyaratBelanja',
            'Create:MasterSyaratBelanja',
            'Update:MasterSyaratBelanja',
            'ViewAny:MasterTujuanBbm',
            'View:MasterTujuanBbm',
            'Create:MasterTujuanBbm',
            'Update:MasterTujuanBbm',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
            'View:LaporanFungsional',
            'View:LaporanLrfk',
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
            'Create:RincianAnggaranBelanja',
            'Update:RincianAnggaranBelanja',
            'Delete:RincianAnggaranBelanja',
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
            'Delete:MasterSubKegiatan',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
            'ViewAny:MasterSyaratBelanja',
            'View:MasterSyaratBelanja',
            'ViewAny:MasterTujuanBbm',
            'View:MasterTujuanBbm',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
            'View:LaporanLrfk',
        ]))->get();
        $pptkRole->syncPermissions($pptkPermissions);

        // === 5. Setup Role JF (Jabatan Fungsional / Pengusul SPJ Bidang) ===
        // Pengusul belanja: buat draf Surat Pesanan, kelola Program, Kegiatan, Sub Kegiatan,
        // Rincian Belanja (RKA), Kode Rekening, Kode Barang (SSH), dan Toko bidangnya.
        $jfPermissions = Permission::whereIn('name', array_merge($basicPegawaiPermissions, [
            'ViewAny:PengajuanSpj',
            'View:PengajuanSpj',
            'Create:PengajuanSpj',
            'Update:PengajuanSpj',
            'Delete:PengajuanSpj',
            'ViewAny:MasterProgram',
            'View:MasterProgram',
            'ViewAny:MasterKegiatan',
            'View:MasterKegiatan',
            'ViewAny:MasterSubKegiatan',
            'View:MasterSubKegiatan',
            'Update:MasterSubKegiatan',
            'ViewAny:RincianAnggaranBelanja',
            'View:RincianAnggaranBelanja',
            'Create:RincianAnggaranBelanja',
            'Update:RincianAnggaranBelanja',
            'Delete:RincianAnggaranBelanja',
            'ViewAny:KodeRekening',
            'View:KodeRekening',
            'Create:KodeRekening',
            'Update:KodeRekening',
            'Delete:KodeRekening',
            'ViewAny:MasterBarang',
            'View:MasterBarang',
            'Create:MasterBarang',
            'Update:MasterBarang',
            'Delete:MasterBarang',
            'ViewAny:MasterToko',
            'View:MasterToko',
            'Create:MasterToko',
            'Update:MasterToko',
            'Delete:MasterToko',
            'ViewAny:MasterSyaratBelanja',
            'View:MasterSyaratBelanja',
            'ViewAny:MasterTujuanBbm',
            'View:MasterTujuanBbm',
            'Create:MasterTujuanBbm',
            'Update:MasterTujuanBbm',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
            'View:LaporanLrfk',
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
            'ViewAny:MasterSyaratBelanja',
            'View:MasterSyaratBelanja',
            'Create:MasterSyaratBelanja',
            'Update:MasterSyaratBelanja',
            'ViewAny:MasterTujuanBbm',
            'View:MasterTujuanBbm',
            'Create:MasterTujuanBbm',
            'Update:MasterTujuanBbm',
            'ViewAny:Bidang',
            'View:Bidang',
            'ViewAny:User',
            'View:LaporanFungsional',
            'View:LaporanLrfk',
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
            'ViewAny:MasterSyaratBelanja',
            'View:MasterSyaratBelanja',
            'ViewAny:MasterTujuanBbm',
            'View:MasterTujuanBbm',
        ]))->get();
        $pegawaiRole->syncPermissions($pegawaiPermissions);

        $this->command?->info('Hak Akses (Permissions) untuk Role Keuangan (PA, PPK, PPTK, Staf Keuangan, Bendahara, Admin, Pegawai) telah berhasil disinkronisasi.');
    }
}
