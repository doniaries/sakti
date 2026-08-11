<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DataPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $data = array (
  0 => 
  array (
    'nama' => 'AFRINELDI, SH',
    'nip' => '19720407 199202 1 002',
    'jabatan' => 'KEPALA DINAS',
    'pendidikan' => 'S1 HUKUM',
    'jk' => 'L',
    'eselon' => 'II.b',
    'tempat_lahir' => 'Padang',
    'tanggal_lahir' => '7 April 1972',
  ),
  1 => 
  array (
    'nama' => 'DESMAWATI, SE. M.Si',
    'nip' => '19691224 199503 2 002',
    'jabatan' => 'SEKRETARIS',
    'pendidikan' => 'S2 KEU DAERAH',
    'jk' => 'P',
    'eselon' => 'III.a',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '24 Desember 1969',
  ),
  2 => 
  array (
    'nama' => 'WILDA ARDES N, ST',
    'nip' => '19730108 200604 2 010',
    'jabatan' => 'KABID PPENGEMBANGAN',
    'pendidikan' => 'S1 TEKNIK SIPIL',
    'jk' => 'P',
    'eselon' => 'III.b',
    'tempat_lahir' => '',
    'tanggal_lahir' => '',
  ),
  4 => 
  array (
    'nama' => 'INDRAWADI. R, SP',
    'nip' => '19800710 200701 1 004',
    'jabatan' => 'KABID PEMUDA DAN',
    'pendidikan' => 'S1 PERTANIAN',
    'jk' => 'P',
    'eselon' => 'III.b',
    'tempat_lahir' => '',
    'tanggal_lahir' => '',
  ),
  6 => 
  array (
    'nama' => 'RAHMAD AZANDI FAJAR, S.STP',
    'nip' => '19890116 201507 1 001',
    'jabatan' => 'Pj.KABID PEMASARAN',
    'pendidikan' => 'D IV PENCATATAN',
    'jk' => 'L',
    'eselon' => 'III.b',
    'tempat_lahir' => '',
    'tanggal_lahir' => '',
  ),
  8 => 
  array (
    'nama' => 'DIKI ZULFIKAR,SS',
    'nip' => '19741230 200604 1 009',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator  Promosi Kepemudaan dan KepemanduanBidang Pemuda dan Olahraga  pada Dinas Pariwisata',
    'pendidikan' => 'S1  S. INGGRIS',
    'jk' => 'L',
    'eselon' => 'IV.a',
    'tempat_lahir' => '',
    'tanggal_lahir' => '',
  ),
  9 => 
  array (
    'nama' => 'SUPARTA, SE. M. Si',
    'nip' => '19780103 200701 1 003',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator  Pengembangan Sumber Daya Pariwisata Bidang Pengembangan Destinasi Pariwisata   pada Dinas Pariwisata',
    'pendidikan' => 'S2 PWD',
    'jk' => 'L',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Sawahlunto',
    'tanggal_lahir' => '03 Januari 1978',
  ),
  10 => 
  array (
    'nama' => 'ZULFITRI, S. Ag',
    'nip' => '19770825 200701 2 006',
    'jabatan' => 'KASUBBAG UMUM DAN',
    'pendidikan' => 'S1 SYARI\'AH',
    'jk' => 'P',
    'eselon' => 'IV.a',
    'tempat_lahir' => '',
    'tanggal_lahir' => '',
  ),
  12 => 
  array (
    'nama' => 'NOFRIZALM, SE',
    'nip' => '19761129 200901 1 002',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator  Promosi Pariwisata Bidang Pemasaran Pariwisata  pada Dinas Pariwisata',
    'pendidikan' => 'S1 EKONOMI',
    'jk' => 'L',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '29-11-1976',
  ),
  13 => 
  array (
    'nama' => 'MIRA APRIYANTI, ME',
    'nip' => '19840426 200902 2 010',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator  Pengembangan Kerjasama dan Investasi Pariwisata Bidang Pengembangan Destinasi Pariwisata pada Dinas Pariwisata',
    'pendidikan' => 'S2 EKONOMI',
    'jk' => 'P',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Jambun',
    'tanggal_lahir' => '26 April 1984',
  ),
  14 => 
  array (
    'nama' => 'WIRA GUSTI MUSTIKA, S, Sn',
    'nip' => '19830824 201001 2 021',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator Pembinaan dan Pegelaran Seni Bidang  Pemasaran Dinas Pariwisata',
    'pendidikan' => 'S1 SASTRA TARI',
    'jk' => 'P',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Muaro',
    'tanggal_lahir' => '08 Agustus 1983',
  ),
  15 => 
  array (
    'nama' => 'JULHARDIANTO, S. Pt. M. Si',
    'nip' => '19841023 200501 1 003',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator Keuangan dan Perencaan pada Sekretariat  Dinas Pariwisata',
    'pendidikan' => 'S2 SAINS',
    'jk' => 'L',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Batu Manjulur',
    'tanggal_lahir' => '23oktober 1984',
  ),
  16 => 
  array (
    'nama' => 'HENDRA PERMANA, SE',
    'nip' => '19780527 201001 1 006',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator Pengembangan Destinasi Pariwisata  Bidang Pengembangan Destinasi Pariwisata',
    'pendidikan' => 'S1 EKONOMI',
    'jk' => 'L',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Pematang Panjang',
    'tanggal_lahir' => '27 Mei 1978',
  ),
  17 => 
  array (
    'nama' => 'RONA YUNITA, S.ST',
    'nip' => '19840616 201101 2 007',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator Ekonomi Kreatif Bidang Ekonomi Pemasaran Pariwisata',
    'pendidikan' => 'D IV PARIWISATA',
    'jk' => 'P',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Payakumbuh',
    'tanggal_lahir' => '16-06-1984',
  ),
  18 => 
  array (
    'nama' => 'LIDEFRIYON, SH',
    'nip' => '19760625 200701 1 005',
    'jabatan' => 'Analis Kebijakan Ahli Muda Sub Koordinator Sarana dan Prasarana Pemuda dan Olahraga Bidang Pemuda dan Olahraga pada Dinas Pariwisata',
    'pendidikan' => 'S1 ILMU HUKUM',
    'jk' => 'L',
    'eselon' => 'IV.a',
    'tempat_lahir' => 'Muaro Gambok',
    'tanggal_lahir' => '25 Juni 1976',
  ),
  19 => 
  array (
    'nama' => 'IMELDA SANGGETA, A.Md',
    'nip' => '19751009 200501 2 002',
    'jabatan' => 'STAF',
    'pendidikan' => 'DIII PARIWISATA',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Tanjung Ampalu',
    'tanggal_lahir' => '09-10-1975',
  ),
  20 => 
  array (
    'nama' => 'INDRA JUNAIDI, S.A.P',
    'nip' => '19740604 201001 1 008',
    'jabatan' => 'STAF',
    'pendidikan' => 'S1 Adm Publik',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '04-06-1974',
  ),
  21 => 
  array (
    'nama' => 'MEVI FEBRIANTI',
    'nip' => '19830210 201212 2 004',
    'jabatan' => 'STAF',
    'pendidikan' => 'S1 Adm Publik',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '10 Februari 1983',
  ),
  22 => 
  array (
    'nama' => 'RONI RIVANDRA',
    'nip' => '19780224 201001 1 003',
    'jabatan' => 'STAF',
    'pendidikan' => 'SMA',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Batu Gandang',
    'tanggal_lahir' => '24 Februari 1978',
  ),
  23 => 
  array (
    'nama' => 'MAIYOLA INDRA, A.Md.A.B',
    'nip' => '19990512 202203 2 013',
    'jabatan' => 'STAF',
    'pendidikan' => 'D3 Administrasi Bisnis',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Padang',
    'tanggal_lahir' => '12 Mei 1999',
  ),
  24 => 
  array (
    'nama' => 'WIWIRNA',
    'nip' => '19850723 200901 2 007',
    'jabatan' => 'STAF',
    'pendidikan' => 'SMA',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Padang Laweh',
    'tanggal_lahir' => '23 Juli 1985',
  ),
  25 => 
  array (
    'nama' => 'ROSNI',
    'nip' => '19850622 201001 2 003',
    'jabatan' => 'STAF',
    'pendidikan' => 'SMA',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Muaro',
    'tanggal_lahir' => '22 Juni 1985',
  ),
  26 => 
  array (
    'nama' => 'NASIB',
    'nip' => '19740408 201001 1 006',
    'jabatan' => 'STAF',
    'pendidikan' => 'SD',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Muaro',
    'tanggal_lahir' => '8 April 1974',
  ),
  27 => 
  array (
    'nama' => 'ARMAN, SP',
    'nip' => '19840515 202321 1 025',
    'jabatan' => 'AHLI PERTAMA-ANALIS KEBIJAKAN',
    'pendidikan' => 'S1 AGROTEKNOLOGI',
    'jk' => 'L',
    'eselon' => '-',
    'tempat_lahir' => 'Kab. Sijunjung',
    'tanggal_lahir' => '15 Mei 1984',
  ),
  28 => 
  array (
    'nama' => 'MUSAIRIL HADI, SE',
    'nip' => '19930117 202321 1 022',
    'jabatan' => 'AHLI PERTAMA-PERENCANA',
    'pendidikan' => 'S2 MANAJEMEN',
    'jk' => 'L',
    'eselon' => '-',
    'tempat_lahir' => 'Kab. Sijunjung',
    'tanggal_lahir' => '17 Januari 1993',
  ),
  29 => 
  array (
    'nama' => 'GUSMAINI, SP',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1 PPERTANIAN',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Muaro Sijunjung',
    'tanggal_lahir' => '10-08-1974',
  ),
  30 => 
  array (
    'nama' => 'DITA TRISIA, SP',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1 PPERTANIAN',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '13 Maret 1986',
  ),
  31 => 
  array (
    'nama' => 'RIKA PUSPA MERI, SP',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1 PERTANIAN',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Unggan',
    'tanggal_lahir' => '10 Oktober 1985',
  ),
  32 => 
  array (
    'nama' => 'DIAN KURNIA SARI, S. Pd',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1 PENDIDIKAN',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '6 Januari 1991',
  ),
  33 => 
  array (
    'nama' => 'ROBBY PITHALOCA, A. Md',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'D III PERHOTELAN',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Jakarta',
    'tanggal_lahir' => '08 Juni 1981',
  ),
  34 => 
  array (
    'nama' => 'SARIMAH MUHKTAR',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'STM',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Gambok',
    'tanggal_lahir' => '8 November 1970',
  ),
  35 => 
  array (
    'nama' => 'INDRA GUSTI',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'MAN',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '17 Agustus 1974',
  ),
  36 => 
  array (
    'nama' => 'RORI FRAND UTAMA',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SMA',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Tikalak',
    'tanggal_lahir' => '27 Mei 1982',
  ),
  37 => 
  array (
    'nama' => 'GUSNEDI ARIANTO',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SMA',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Muaro',
    'tanggal_lahir' => '05 Agustus 1989',
  ),
  38 => 
  array (
    'nama' => 'YULHENDRI',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SMA',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Swl Sijunjung',
    'tanggal_lahir' => '22 Juli 1990',
  ),
  39 => 
  array (
    'nama' => 'TRIJONI ANDESPI',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SMA',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Taratak',
    'tanggal_lahir' => '13 April 1991',
  ),
  40 => 
  array (
    'nama' => 'ROBBY DARWIS',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'PAKET B',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Muaro',
    'tanggal_lahir' => '12 November 1974',
  ),
  41 => 
  array (
    'nama' => 'ANI SUSI SUSANTI, SP',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1 PETERNAKAN',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Muaro',
    'tanggal_lahir' => '4 September 1988',
  ),
  42 => 
  array (
    'nama' => 'BUDI SETIA ROMANDO, S. Si',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1 KEOLAHRAGAAN',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Swl Sijunjung',
    'tanggal_lahir' => '28 Maret 1990',
  ),
  43 => 
  array (
    'nama' => 'EKO SAPUTRA',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'PAKET C',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Silokek',
    'tanggal_lahir' => '21 Juli 1989',
  ),
  44 => 
  array (
    'nama' => 'NOVA YUSNITA',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'PAKET C',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Solok',
    'tanggal_lahir' => '01 November 1995',
  ),
  45 => 
  array (
    'nama' => 'FAUZI',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SMK',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '30 Maret 1980',
  ),
  46 => 
  array (
    'nama' => 'ERNI GUSRITA',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SLTA',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Silokek',
    'tanggal_lahir' => '10 Januari 1991',
  ),
  47 => 
  array (
    'nama' => 'MANTAPRIL',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SLTA',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Silokek',
    'tanggal_lahir' => '04 Mei 1977',
  ),
  48 => 
  array (
    'nama' => 'PRADIUTAMA JULIYANSA, SH',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1 HUKUM',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Sawahlunto',
    'tanggal_lahir' => '22 Juli 1994',
  ),
  49 => 
  array (
    'nama' => 'FEBRIWAHYUDI',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SLTA',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Bangkinang',
    'tanggal_lahir' => '18 Februari 1990',
  ),
  50 => 
  array (
    'nama' => 'NONI WILDA SARI, M.Si',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S2',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Solok',
    'tanggal_lahir' => '23 Juni 1994',
  ),
  51 => 
  array (
    'nama' => 'RAHMA DONI, S.Pd',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SI',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '03 Maret 1991',
  ),
  52 => 
  array (
    'nama' => 'NINI RIGI, S.P',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SI',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Sijunjung',
    'tanggal_lahir' => '29 Mei 1996',
  ),
  53 => 
  array (
    'nama' => 'FEBRI HERLINA, SM',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SI',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Guguak Nenas',
    'tanggal_lahir' => '18 Februari 1998',
  ),
  54 => 
  array (
    'nama' => 'SEPTIA JUMAIDAR',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SLTA',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Muaro',
    'tanggal_lahir' => '27 September 1996',
  ),
  55 => 
  array (
    'nama' => 'RAHMA DEWI',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SLTA',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Muaro Sijunjung',
    'tanggal_lahir' => '15 Januari 2002',
  ),
  56 => 
  array (
    'nama' => 'RAHMA KENI',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SLTA',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Pasaman',
    'tanggal_lahir' => '04 April 1999',
  ),
  57 => 
  array (
    'nama' => 'SIZIAH JUANDRI, S.Pd',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'SI',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Durian Gadang',
    'tanggal_lahir' => '02 Juli 1997',
  ),
  58 => 
  array (
    'nama' => 'FENDRA WINATA, A.Ma',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'DIII',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Mundam Sakti',
    'tanggal_lahir' => '02 Februari 1993',
  ),
  59 => 
  array (
    'nama' => 'ANDRA PEBTALIYUS, S. Sn',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1',
    'jk' => 'L',
    'eselon' => '',
    'tempat_lahir' => 'Tanjung Gadang',
    'tanggal_lahir' => '13 Februari 1992',
  ),
  60 => 
  array (
    'nama' => 'FORTUNA ANGGINI ADVI, S.M.,Par',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'S1',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Solok',
    'tanggal_lahir' => '26 Maret 1999',
  ),
  61 => 
  array (
    'nama' => 'DINDA PUTRI WENY, S.ST',
    'nip' => '-',
    'jabatan' => 'THL',
    'pendidikan' => 'D4',
    'jk' => 'P',
    'eselon' => '',
    'tempat_lahir' => 'Lintau',
    'tanggal_lahir' => '14 Juli 1998',
  ),
);

        foreach ($data as $row) {
            // NIP extraction or generation if empty
            $nip = trim(str_replace('NIP.', '', $row['nip']));
            $nip = trim(str_replace(' ', '', $nip));
            
            // Nama parsing
            $nama = $row['nama'];

            // Jabatan creation/assignment
            $jabatanStr = $row['jabatan'];
            $jabatanId = null;
            if (!empty($jabatanStr)) {
                $jabatan = Jabatan::firstOrCreate(
                    ['nama' => $jabatanStr],
                    ['slug' => Str::slug($jabatanStr)]
                );
                $jabatanId = $jabatan->id;
            }

            $email = strtolower(Str::slug($nama)) . '@gmail.com';
            
            // create unique email/NIP if exist
            if (empty($nip) || $nip == '-') {
                $nip = 'THL' . rand(100000, 999999);
            }

            // Ensure unique email
            $count = User::query()->where('email', $email)->count();
            if ($count > 0) {
                $email = strtolower(Str::slug($nama)) . $count . '@gmail.com';
            }

            $user = User::firstOrCreate(
                ['nip' => $nip],
                [
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make('password'), // default password
                    'jabatan_id' => $jabatanId,
                    'tempat_lahir' => !empty($row['tempat_lahir']) ? $row['tempat_lahir'] : null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? ''),
                    'pendidikan' => !empty($row['pendidikan']) ? $row['pendidikan'] : null,
                    'jenis_kelamin' => !empty($row['jk']) ? $row['jk'] : null,
                    'eselon' => !empty($row['eselon']) ? $row['eselon'] : null,
                    'aktif' => true,
                ]
            );
            
            // Assign role pegawai
            if (!$user->hasRole('pegawai')) {
                $user->assignRole('pegawai');
            }
        }
        
        $this->command->info('Data pegawai berhasil di-seed.');
    }

    private function parseDate(string $dateStr)
    {
        // Simple mapping from indonesian month to date format or return null
        if (empty($dateStr)) return null;
        
        $months = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03',
            'April' => '04', 'Mei' => '05', 'Juni' => '06',
            'Juli' => '07', 'Agustus' => '08', 'September' => '09',
            'Oktober' => '10', 'November' => '11', 'Desember' => '12'
        ];

        foreach ($months as $name => $num) {
            $dateStr = str_ireplace($name, $num, $dateStr);
        }
        
        $parts = explode(' ', trim($dateStr));
        if (count($parts) == 3) {
            // Assume format: dd mm yyyy
            return $parts[2] . '-' . $parts[1] . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        }
        
        return null;
    }
}