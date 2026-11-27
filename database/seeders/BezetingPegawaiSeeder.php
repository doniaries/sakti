<?php

namespace Database\Seeders;

use App\Enums\Eselon;
use App\Enums\GolonganRuang;
use App\Enums\JenjangPendidikan;
use App\Models\RiwayatGolongan;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPendidikan;
use App\Models\RiwayatSertifikat;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder DAFTAR NOMINATIF / BEZETING PEGAWAI ASN DISPARPORA KAB. SIJUNJUNG TAHUN 2026.
 * Mengisi lengkap data kepegawaian 80 pegawai ke tabel users, riwayat_golongans,
 * riwayat_jabatans, riwayat_pendidikans, dan riwayat_sertifikats (PNJJ/Diklat).
 */
class BezetingPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // 1-22: PNS
            ['19720407 199202 1 002', 'AFRINELDI, SH', 'Padang', '1972-04-07', 'L', 'II.b', '1992-02-01', '1993-02-01', 'IV/c', '2024-10-01', 27, 8, 'KEPALA DINAS', '822.22/429/BKPSDM-2019', '2019-05-13', '2019-05-08', 'S1', 'HUKUM', 2003, 'PIM II', 2022, 923],
            ['19691224 199503 2 002', 'DESMAWATI, SE. M.Si', 'Sijunjung', '1969-12-24', 'P', 'III.a', '1995-03-01', '1996-01-01', 'IV/b', '2011-01-10', 24, 9, 'SEKRETARIS', '821.23/39/BKPSDM-2019', '2019-05-08', '2019-05-08', 'S2', 'KEU DAERAH', 2001, 'PIM III', 2023, 500],
            ['19730108 200604 2 010', 'WILDA ARDES N, MT', 'Sisawah', '1973-01-08', 'P', 'III.b', '2006-04-01', '2007-12-01', 'IV/a', '2024-02-01', 22, 10, 'KABID PENGEMBANGAN DESTINASI PARIWISATA', '821.23/032/BKPSDM-2022', '2022-04-28', '2022-04-28', 'S2', 'MAGISTER TEKNIK SIPIL', 2023, 'PIM IV', 2012, 285],
            ['19800710 200701 1 004', 'INDRAWADI. R, SP', 'Ampang Kuranji', '1980-07-10', 'P', 'III.b', '1989-01-01', '2007-11-01', 'IV/a', '2026-04-01', 19, 9, 'KABID PEMUDA DAN OLAHRAGA', '821.23/194/BKPSDM-2021', '2021-09-23', '2021-09-23', 'S1', 'PERTANIAN', 2007, 'PIM IV', 2017, 893],
            ['19820414 200212 1 003', 'DONI ARIES, S.IP, M.CIO', 'Muaro Sijunjung', '1982-04-14', 'L', 'III.b', '2002-12-01', '2002-03-01', 'IV/a', '2026-04-01', 18, 4, 'KABID PEMASARAN PARIWISATA', '821.23/025/BKPSDM-2026', '2026-02-02', '2026-02-02', 'S2', 'CHIEF INFORMATION OFFICER', 2015, 'LPJ', 2003, 135],
            ['19741230 200604 1 009', 'DIKI ZULFIKAR, SS', 'Tanjung Ampalu', '1974-12-30', 'L', 'IV.a', '2006-11-27', '2007-11-15', 'III/d', '2016-10-01', 16, 4, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'S1', 'S. INGGRIS', 1999, 'PIM IV', 2012, 285],
            ['19770825 200701 2 006', 'ZULFITRI, S. Ag', 'Lubuk Tarab', '1977-08-25', 'P', 'IV.a', '2007-01-01', '2008-11-01', 'III/d', '2019-04-01', 16, 1, 'Kasubbag Umum dan Kepegawaian', '821.24/195/BKPSDM-2021', '2021-09-24', '2021-09-24', 'S1', "SYARI'AH", 2000, 'PIM IV', 2016, 881],
            ['19761129 200901 1 002', 'NOFRIZAL M, SE', 'Sijunjung', '1976-11-29', 'L', 'IV.a', '2009-01-01', '2010-01-01', 'III/d', '2021-04-01', 18, 4, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'S1', 'EKONOMI', 2000, 'PIM IV', 2018, 893],
            ['19840426 200902 2 010', 'MIRA APRIYANTI, ME', 'Jambun', '1984-04-26', 'P', 'IV.a', '2009-02-01', '2010-10-01', 'III/d', '2021-04-01', 10, 10, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'S2', 'EKONOMI', 2015, 'PIM IV', 2017, 893],
            ['19830824 201001 2 021', 'WIRA GUSTI MUSTIKA, S.Sn', 'Muaro', '1983-08-24', 'P', 'IV.a', '2010-01-01', '2011-07-01', 'III/d', '2022-04-01', 12, 3, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'S1', 'SASTRA TARI', 2005, 'PIM IV', 2017, 893],
            ['19841023 200501 1 003', 'JULHARDIANTO, S. Pt. M. Si', 'Batu Manjulur', '1984-10-23', 'L', 'IV.a', '2005-01-02', '2006-05-10', 'III/d', '2025-04-01', 15, 3, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'S2', 'SAINS', 2017, 'LPJ', 2005, 91],
            ['19780527 201001 1 006', 'HENDRA PERMANA, SE', 'Pematang Panjang', '1978-05-27', 'L', 'IV.a', '2010-01-01', '2011-07-01', 'III/d', '2022-04-01', 17, 10, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'S1', 'EKONOMI PEMBANGUNAN', 2002, 'LPJ', 2010, 230],
            ['19840616 201101 2 007', 'RONA YUNITA, S.ST', 'Payakumbuh', '1984-06-16', 'P', 'IV.a', '2011-01-01', '2012-10-01', 'III/d', '2023-04-01', 12, 3, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'D4', 'PARIWISATA', 2007, 'LPJ', 2011, 216],
            ['19760625 200701 1 005', 'LIDEFRIYON, SH', 'Muaro Gambok', '1976-06-25', 'L', 'IV.a', '2007-01-01', '2008-11-01', 'III/d', '2025-10-01', 20, 9, 'Analis Kebijakan Ahli Muda', '821.25/234/BKPSDM-2021', '2021-12-31', '2021-12-31', 'S1', 'ILMU HUKUM', 2013, 'LPJ', 2008, 174],
            ['19751009 200501 2 002', 'IMELDA SANGGETA, A.Md', 'Tanjung Ampalu', '1975-10-09', 'P', null, '2005-01-01', '2006-06-01', 'III/c', '2021-04-01', 14, 3, 'Pengelola Layanan Operasional', '823.3/141/BKPSDM-2021', '2021-03-31', '2021-04-01', 'D3', 'PARIWISATA', 1998, 'LPJ', 2005, 90],
            ['19740604 201001 1 008', 'INDRA JUNAIDI, S.A.P', 'Sijunjung', '1974-06-04', 'L', null, '2010-01-01', '2011-07-01', 'III/b', '2023-10-01', 15, 1, 'Penelaah Teknis Kebijakan', '823.3/65/BKPSDM-2019', '2019-08-08', '2019-08-08', 'S1', 'Adm Publik', 2018, 'LPJ', 2010, 174],
            ['19830210 201212 2 004', 'MEVI FEBRIANTI', 'Sijunjung', '1983-02-10', 'P', null, '2012-12-01', '2015-04-01', 'III/a', '2023-10-01', 14, 3, 'Penelaah Teknis Kebijakan', '823.2/146/BKPSDM-2023', '2023-09-18', '2023-09-18', 'S1', 'Adm Publik', 2023, 'LPJ', 2015, 69],
            ['19780224 201001 1 003', 'RONI RIVANDRA', 'Batu Gandang', '1978-02-24', 'L', null, '2010-01-01', '2011-07-01', 'II/d', '2022-04-01', 18, 8, 'Pengadministrasi Perkantoran', '821.29/01/BKPSDM-2017', '2017-01-06', '2017-01-06', 'SLTA', 'SMA', 1997, 'LPJ', 2010, 174],
            ['19990512 202203 2 013', 'MAIYOLA INDRA, A.Md.A.B', 'Padang', '1999-05-12', 'P', null, '2022-03-01', '2023-12-01', 'II/c', '2023-12-01', 4, 9, 'Pengelola Layanan Operasional', '821.12/171/BKPSDM-2023', '2023-11-22', '2023-11-22', 'D3', 'Administrasi Bisnis', 2020, 'LATSAR', 2023, 647],
            ['19850723 200901 2 007', 'WIWIRNA', 'Padang Laweh', '1985-07-23', 'P', null, '2009-01-01', '2010-10-01', 'II/d', '2026-04-01', 19, 9, 'Pengadministrasi Perkantoran', '821.29/01/BKPSDM-2017', '2017-01-06', '2017-01-06', 'SLTA', 'SMA', 2010, 'LPJ', 2009, 174],
            ['19850622 201001 2 003', 'ROSNI', 'Muaro', '1985-06-22', 'P', null, '2010-01-01', '2011-07-01', 'II/c', '2022-04-01', 13, 4, 'Pengadministrasi Perkantoran', '821.29/01/BKPSDM-2017', '2017-01-06', '2017-01-06', 'SLTA', 'SMA', 2010, 'LPJ', 2010, 174],
            ['19740408 201001 1 006', 'NASIB', 'Muaro', '1974-04-08', 'L', null, '2010-01-01', '2011-07-01', 'II/b', '2025-02-01', 15, 6, 'Operator Layanan Operasional', '821.29/01/BKPSDM-2017', '2017-01-06', '2017-01-06', 'SLTA', 'PAKET C', 2010, 'LPJ', 2010, 174],

            // 23-41: CPNS 2025
            ['19910217 202506 2 002', 'FITRI AMELIA, S. Pd', 'Kota Padang Panjang', '1991-02-17', 'P', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Penerjemah Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'PDKK BAHASA INGGRIS', 2014, null, null, null],
            ['19920106 202506 2 003', 'ROSY WITRIN, SST', 'Kota Padang', '1992-01-06', 'P', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Analis Kebijakan Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'D4', 'MANAJEMEN PERHOTELAN', 2014, null, null, null],
            ['19930825 202506 1 001', 'AGUSMEDI ARIF, S.Kom', 'Kab. Sijunjung', '1993-08-25', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Penata Kelola Sistem dan Teknologi', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'SISTEM INFORMASI', 2015, null, null, null],
            ['19950108 202506 2 007', 'MELGASARI, S.Pd', 'Kota Padang', '1995-01-08', 'P', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Penerjemah Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'PDDK BAHASA INGGRIS', 2017, null, null, null],
            ['19950306 202506 1 002', 'AKBAR MALIK, S.AB', 'Kab. Tanah Datar', '1995-03-06', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Analis Kebijakan Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'ILMU ADMINISTRASI BISNIS', 2018, null, null, null],
            ['19951111 202506 1 004', 'RICO ANGGALA, S.Or', 'Kab. Solok', '1995-11-11', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Pelatih Olahraga Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'ILMU KEOLAHRAGAAN', 2018, null, null, null],
            ['19960704 202506 1 002', 'DIO TRI ANANDA PUTRA, S.T', 'Kota Solok', '1996-07-04', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Pranata Komputer Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'TEKNIK KOMPUTER', 2019, null, null, null],
            ['19990331 202506 1 005', 'MAULANA IBRAHIM, S.T', 'Kota Sawahlunto', '1999-03-31', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Konservator', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'TEKNIK SIPIL', 2021, null, null, null],
            ['19990809 202506 1 004', 'HADI ALFADJRI, S.T', 'Kab. Pesisir Selatan', '1999-08-09', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Konservator', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'TEKNIK SIPIL', 2021, null, null, null],
            ['19991227 202506 1 003', 'FARHANUL HUDA, S.Par', 'Kota Padang Panjang', '1999-12-27', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Analis Kebijakan Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'INDUSTRI PERJALANAN WISATA', 2021, null, null, null],
            ['20000905 202506 1 006', 'NAZHIRUL RIFQI ALNEV, S.Tr.Par', 'Kota Solok', '2000-09-05', 'L', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Analis Kebijakan Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'D4', 'USAHA PERJALANAN WISATA', 2023, null, null, null],
            ['20020628 202506 2 013', 'AQILLA FADIA HAYA, S.Ak', 'Kab. Sijunjung', '2002-06-28', 'P', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Analis Kebijakan Ahli Pertama', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'S1', 'AKUNTANSI', 2024, null, null, null],
            ['19900112 202506 1 001', 'JENI ZULMARLY KAMIL', 'Kab. Pesisir Selatan', '1990-11-26', 'L', null, '2025-06-01', null, 'II/a', '2025-06-01', 0, 0, 'Asisten Pelatih Olahraga Pemula', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'SLTA', 'SLTA', 1990, null, null, null],
            ['19960531 202506 1 007', 'RANES MUHAMMAD FIQRI', 'Kota Solok', '1996-05-31', 'L', null, '2025-06-01', null, 'II/a', '2025-06-01', 0, 0, 'Asisten Pelatih Olahraga Pemula', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'SLTA', 'SLTA', 2014, null, null, null],
            ['19990603 202506 1 001', 'SYAFRIWAL AM', 'Kab. Sijunjung', '1999-06-03', 'L', null, '2025-06-01', null, 'II/a', '2025-06-01', 0, 0, 'Asisten Pelatih Olahraga Pemula', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'SLTA', 'SLTA', 2014, null, null, null],
            ['20011021 202506 2 003', 'SALSABILA MUKHSIS', 'Kota Bukittinggi', '2001-10-21', 'P', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Asisten Pelatih Olahraga Pemula', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'SLTA', 'SLTA', 2020, null, null, null],
            ['20011127 202506 1 004', 'FADILAH SAPUTRA', 'Kab. Kampar', '2001-11-27', 'L', null, '2025-06-01', null, 'II/a', '2025-06-01', 0, 0, 'Asisten Pelatih Olahraga Pemula', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'SLTA', 'SLTA', 2020, null, null, null],
            ['20030206 202506 1 001', 'RAGANDHI TANLIN', 'Kota Padang', '2003-02-06', 'L', null, '2025-06-01', null, 'II/a', '2025-06-01', 0, 0, 'Asisten Pelatih Olahraga Pemula', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'SLTA', 'SLTA', 2021, null, null, null],
            ['20050222 202506 2 001', 'SYIFA SALSABILA', 'Kota Sawahlunto', '2005-02-22', 'P', null, '2025-06-01', null, 'III/a', '2025-06-01', 0, 0, 'Asisten Pelatih Olahraga Pemula', '813/64BKPSDM-2025', '2025-05-30', '2025-06-01', 'SLTA', 'SLTA', 2023, null, null, null],

            // 42-80: PPPK
            ['19840515 202321 1 025', 'ARMAN, SP', 'Kab. Sijunjung', '1984-05-15', 'L', '-', '2023-11-01', null, 'IX', '2023-11-01', null, null, 'Ahli Pertama-Analis Kebijakan', '821.29/166/BKPSDM-2023', '2023-11-13', '2023-11-01', 'S1', 'PERTANIAN', 2010, null, null, null, true],
            ['19930117 202321 1 022', 'MUSAIRIL HADI, SE', 'Kab. Sijunjung', '1993-01-17', 'L', '-', '2023-11-01', null, 'IX', '2023-11-01', null, null, 'Ahli Pertama-Perencana', '821.29/166/BKPSDM-2023', '2023-11-13', '2023-11-01', 'S1', 'EKONOMI', 2021, null, null, null, true],
            ['19740810 202521 2 013', 'GUSMAINI, SP', 'Muaro Sijunjung', '1974-08-10', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'PERTANIAN', 2013, null, null, null, true],
            ['19860313 202521 2 035', 'DITA TRISIA, SP', 'Sijunjung', '1986-03-13', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'PERTANIAN', 2013, null, null, null, true],
            ['19851011 202521 2 022', 'RIKA PUSPA MERI, SP', 'Unggan', '1985-10-11', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'AGRIBISNIS', 2014, null, null, null, true],
            ['19910106 202521 2 039', 'DIAN KURNIA SARI, S. Pd', 'Sijunjung', '1991-01-06', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'PENDIDIKAN JASMANI KESEHATAN & REKREASI', 2015, null, null, null, true],
            ['19880904 202521 2 043', 'ANI SUSI SUSANTI, SP', 'Muaro', '1988-09-04', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'PETERNAKAN', 2014, null, null, null, true],
            ['19900328 202521 1 032', 'BUDI SETIA ROMANDO, S. Si', 'Swl Sijunjung', '1990-03-28', 'L', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'ILMU KEOLAHRAGAAN', 2014, null, null, null, true],
            ['19940623 202521 2 038', 'NONI WILDA SARI, S.Kom.I', 'Solok', '1994-06-23', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Pranata Hubungan Masyarakat Ahli Pertama', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'KOMUNIKASI DAN PENYIARAN ISLAM', 2016, null, null, null, true],
            ['19940722 202521 2 026', 'PRADIUTAMA JULIYANSA, SH', 'Sawahlunto', '1994-07-22', 'L', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'ILMU HUKUM', 2018, null, null, null, true],
            ['19910303 202521 1 044', 'RAHMA DONI, S.Pd', 'Sijunjung', '1991-03-03', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'PENDIDIKAN GEOGRAFI', 2014, null, null, null, true],
            ['19960529 202521 2 038', 'NINI RIGI, S.P', 'Sijunjung', '1996-05-29', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'AGRIBISNIS', 2020, null, null, null, true],
            ['19980218 202521 2 019', 'FEBRI HERLINA, SM', 'Guguak Nenas', '1998-02-18', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'MANAJEMEN', 2020, null, null, null, true],
            ['19920213 202521 1 034', 'ANDRA PEBTALIYUS, S. Sn', 'Tanjung Gadang', '1992-02-13', 'L', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Adyatama Kepariwisataan dan Ekonomi Kreatif Ahli Pertama', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'SENI KARAWITAN', 2015, null, null, null, true],
            ['19980727 202521 2 036', 'WIRDATUR AINI, SP', 'Kab. Sijunjung', '1988-07-27', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'AGROTEKNOLOGI', 2021, null, null, null, true],
            ['19980319 202521 2 019', 'AULIA PUTRI BINTANI, S.Pt', 'Kota Tanjung Pinang', '1998-03-19', 'P', null, '2025-10-01', null, 'IX', '2025-10-01', null, null, 'Penata Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'S1', 'PETERNAKAN', 2020, null, null, null, true],
            ['19810608 202521 1 025', 'ROBBY PITHALOCA, A. Md', 'Jakarta', '1981-06-08', 'L', null, '2025-10-01', null, 'VII', '2025-10-01', null, null, 'Pengelola Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'D3', 'PERHOTELAN', 2005, null, null, null, true],
            ['19701108 202521 2 006', 'SARIMAH MUHKTAR', 'Gambok', '1970-11-08', 'P', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 1990, null, null, null, true],
            ['19740817 202521 2 014', 'INDRA GUSTI', 'Sijunjung', '1974-08-17', 'P', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 1995, null, null, null, true],
            ['19820527 202521 1 027', 'RORI FRAND UTAMA', 'Tikalak', '1982-05-27', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Operator Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SMA', 2000, null, null, null, true],
            ['19890805 202521 1 044', 'GUSNEDI ARIANTO', 'Muaro', '1989-08-05', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SMA', 2009, null, null, null, true],
            ['19900722 202521 1 026', 'YULHENDRI', 'Swl Sijunjung', '1990-07-22', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SMA', 2010, null, null, null, true],
            ['19910413 202521 1 035', 'TRIJONI ANDESPI', 'Taratak', '1991-04-13', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SMA', 2010, null, null, null, true],
            ['19890721 202521 1 033', 'EKO SAPUTRA', 'Silokek', '1989-07-21', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SMA', 2010, null, null, null, true],
            ['19951101 202521 2 041', 'NOVA YUSNITA', 'Solok', '1995-11-01', 'P', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 2014, null, null, null, true],
            ['19800330 202521 1 024', 'FAUZI', 'Sijunjung', '1980-03-30', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 1999, null, null, null, true],
            ['19770504 202521 1 022', 'MANTAPRIL', 'Silokek', '1977-05-04', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 2010, null, null, null, true],
            ['19900218 202521 1 030', 'FEBRIWAHYUDI', 'Bangkinang', '1990-02-18', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Operator Layanan Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 2009, null, null, null, true],
            ['19960927 202521 2 032', 'SEPTIA JUMAIDAR', 'Muaro', '1996-09-27', 'P', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 2015, null, null, null, true],
            ['19990404 202521 2 018', 'RAHMA KENI', 'Pasaman', '1999-04-04', 'P', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 2017, null, null, null, true],
            ['20020115 202521 2 003', 'RAHMA DEWI', 'Muaro Sijunjung', '2002-01-15', 'P', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SMA IPS', 2020, null, null, null, true],
            ['19920610 202521 2 063', 'METRI', 'Kab. Sijunjung', '1992-06-10', 'P', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'SLTA', 2016, null, null, null, true],
            ['19741112 202521 1 001', 'ROBBY DARWIS', 'Muaro', '1974-11-12', 'L', null, '2025-10-01', null, 'V', '2025-10-01', null, null, 'Pengadministrasi Perkantoran', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTA', 'PAKET C', 2022, null, null, null, true],
            ['19940714 202521 1 049', 'ASRINA', 'Kab. Sijunjung', '1994-07-14', 'P', null, '2025-10-01', null, 'III', '2025-10-01', null, null, 'Pengelola Umum Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTP', 'MADRASAH TSANAWIYAH', 2010, null, null, null, true],
            ['19970228 202521 1 031', 'DEWI FEBRIANI', 'Kab. Sijunjung', '1994-07-14', 'P', null, '2025-10-01', null, 'III', '2025-10-01', null, null, 'Pengelola Umum Operasional', '821.29/93/BKPSDM-2025', '2025-08-12', '2025-10-01', 'SLTP', 'SLTP', 2014, null, null, null, true],
            ['19970702 202521 2 093', 'SIZIAH JUANDRI, S.Pd', 'Kab. Sijunjung', '1997-07-02', 'P', null, '2025-11-01', null, '-', '2025-11-01', null, null, 'Penata Layanan Operasional', '821.29/161/BKPSDM-2025', '2025-11-27', '2025-11-01', 'S1', 'PDDK BAHASA INGGRIS', 2020, null, null, null, true],
            ['19910110 202521 2 130', 'DINDA PUTRI WENY, S.ST', 'Lintau', '1998-07-14', 'P', null, '2025-11-01', null, '-', '2025-11-01', null, null, 'Penata Layanan Operasional', '821.29/161/BKPSDM-2025', '2025-11-27', '2025-11-01', 'D4', 'MANAJEMEN PERHOTELAN', 2017, null, null, null, true],
            ['19930202 202521 1 126', 'FENDRA WINATA, A.Ma', 'Kab. Sijunjung', '1993-02-02', 'L', null, '2025-11-01', null, '-', '2025-11-01', null, null, 'Operator Layanan Operasional', '821.29/161/BKPSDM-2025', '2025-11-27', '2025-11-01', 'D3', 'SMK MULTIMEDIA', 2015, null, null, null, true],
            ['19910110 202521 2 131', 'ERNI GUSRITA', 'Silokek', '1991-01-10', 'P', null, '2025-11-01', null, '-', '2025-11-01', null, null, 'Operator Layanan Operasional', '821.29/161/BKPSDM-2025', '2025-11-27', '2025-11-01', 'SLTA', 'Madrasah Aliyah IPS', 2011, null, null, null, true],
        ];

        $users = User::query()->bukanSuperAdmin()->get();
        $matched = 0;

        foreach ($rows as $r) {
            [$nip, $nama, $tmpLahir, $tglLahir, $jk, $eselon, $tmtCpns, $tmtPns, $gol, $tmtGol, $mkThn, $mkBln, $jabatan, $skNo, $skTgl, $skTmt, $jenjang, $jurusan, $thnLulus, $pnjjNama, $pnjjTahun, $pnjjJam] = $r;
            $isPppk = (bool) ($r[22] ?? false);

            $user = $users->first(fn (User $u): bool => preg_replace('/\s+/', '', (string) $u->nip) === preg_replace('/\s+/', '', $nip))
                ?? $users->first(function (User $u) use ($nama): bool {
                    $uBase = strtoupper(trim(strstr($u->name, ',', true) ?: $u->name));
                    $nBase = strtoupper(trim(strstr($nama, ',', true) ?: $nama));

                    return $uBase === $nBase;
                });

            if (! $user) {
                continue;
            }
            $matched++;

            $user->update([
                'tempat_lahir' => $tmpLahir,
                'tanggal_lahir' => $tglLahir,
                'jenis_kelamin' => $jk,
                'agama' => 'Islam',
                'eselon' => $eselon ? Eselon::tryFrom($eselon)?->value : null,
                'pangkat_golongan' => $gol !== '-' ? $gol : $user->pangkat_golongan,
                'tmt_pangkat' => $tmtGol,
                'jenjang_pendidikan' => JenjangPendidikan::tryFrom($jenjang)?->value ?? $user->jenjang_pendidikan,
                'pendidikan' => $jurusan,
            ]);

            $hasSeparateKenaikanPangkat = ! $isPppk && $tmtPns !== null;

            // Bersihkan duplikat CPNS yang sebelumnya masuk ke kenaikan_pangkat
            if (! $hasSeparateKenaikanPangkat) {
                RiwayatGolongan::query()
                    ->where('user_id', $user->id)
                    ->where('jenis_sk', 'kenaikan_pangkat')
                    ->delete();
            }

            if ($tmtCpns) {
                $isCpnsLatest = ! $hasSeparateKenaikanPangkat;
                RiwayatGolongan::updateOrCreate(
                    ['user_id' => $user->id, 'jenis_sk' => $isPppk ? 'pppk' : 'cpns'],
                    [
                        'golongan' => $isCpnsLatest ? (GolonganRuang::tryFrom($gol)?->value ?? '-') : '-',
                        'tmt_golongan' => $tmtCpns,
                        'is_golongan_terakhir' => $isCpnsLatest,
                        'masa_kerja_tahun' => $isCpnsLatest ? $mkThn : null,
                        'masa_kerja_bulan' => $isCpnsLatest ? $mkBln : null,
                    ]
                );
            }

            if ($tmtPns) {
                RiwayatGolongan::updateOrCreate(
                    ['user_id' => $user->id, 'jenis_sk' => 'pns'],
                    [
                        'golongan' => '-',
                        'tmt_golongan' => $tmtPns,
                        'is_golongan_terakhir' => false,
                    ]
                );
            }

            if ($hasSeparateKenaikanPangkat) {
                RiwayatGolongan::updateOrCreate(
                    ['user_id' => $user->id, 'jenis_sk' => 'kenaikan_pangkat', 'tmt_golongan' => $tmtGol],
                    [
                        'golongan' => GolonganRuang::tryFrom($gol)?->value ?? '-',
                        'masa_kerja_tahun' => $mkThn,
                        'masa_kerja_bulan' => $mkBln,
                        'is_golongan_terakhir' => true,
                    ]
                );
            }

            RiwayatJabatan::updateOrCreate(
                ['user_id' => $user->id, 'nama_jabatan' => $jabatan, 'tmt_jabatan' => $skTmt],
                [
                    'nomor_sk' => $skNo,
                    'tanggal_sk' => $skTgl,
                    'is_jabatan_sekarang' => true,
                ]
            );

            // Hanya simpan pendidikan terakhir sesuai lampiran bezeting
            $jenjangVal = JenjangPendidikan::tryFrom($jenjang)?->value ?? 'SLTA';
            RiwayatPendidikan::query()
                ->where('user_id', $user->id)
                ->where('jenjang', '!=', $jenjangVal)
                ->delete();

            RiwayatPendidikan::updateOrCreate(
                ['user_id' => $user->id, 'jenjang' => $jenjangVal],
                [
                    'nama_institusi' => '-',
                    'jurusan' => $jurusan,
                    'tahun_lulus' => $thnLulus,
                    'is_pendidikan_terakhir' => true,
                ]
            );

            if ($pnjjNama) {
                RiwayatSertifikat::updateOrCreate(
                    ['user_id' => $user->id, 'jenis' => 'pnjj', 'nama_sertifikat' => $pnjjNama],
                    [
                        'tanggal_terbit' => null,
                        'tahun' => $pnjjTahun,
                        'jumlah_jam' => $pnjjJam,
                    ]
                );
            }
        }

        $this->command?->info("BezetingPegawaiSeeder selesai: {$matched} pegawai diperbarui lengkap sesuai bezeting 2026.");
    }
}
