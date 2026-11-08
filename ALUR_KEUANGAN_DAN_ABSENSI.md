# 📊 Alur Kerja Terpadu Aplikasi SAKTI: Keuangan & Presensi (Absensi)

Dokumen ini menyajikan peta visual dan penjelasan terstruktur untuk dua modul inti dalam aplikasi **SAKTI (Sistem Administrasi, Kinerja, dan Tata Informasi)**:
1. **Modul Pengelolaan Keuangan & SPJ Terpadu** (Alur 2 Tahap: Nota Pesanan hingga Pencairan SPJ).
2. **Modul Presensi & Kinerja Pegawai** (Jendela Absensi Masuk/Pulang hingga Validasi LHK Atasan).

---

## 1. Alur Modul Keuangan & SPJ Terpadu

Modul keuangan mengadopsi prinsip **2 Tahap Belanja Daerah**:
- **Tahap 1**: Persetujuan Pemesanan oleh PPTK (Kabid) sebelum belanja dilakukan.
- **Tahap 2**: Realisasi Pembelian fisik, verifikasi administrasi PPK & PA, penerbitan Kwitansi/NPD, dan pencairan kas oleh Bendahara Pengeluaran.

```mermaid
flowchart TD
    classDef dasar fill:#f8fafc,stroke:#64748b,stroke-width:1.5px,color:#1e293b;
    classDef aksi fill:#dbeafe,stroke:#2563eb,stroke-width:2px,color:#1e3a8a;
    classDef verif fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#78350f;
    classDef sukses fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#14532d;
    classDef tolak fill:#fee2e2,stroke:#dc2626,stroke-width:2px,color:#7f1d1d;
    classDef dokumen fill:#f3e8ff,stroke:#9333ea,stroke-width:2px,color:#581c87;

    subgraph FASE0["0. Pondasi Anggaran (Master RKA SIPD)"]
        RKA["Input Master DPA / RKA SIPD<br/>• Program & Kegiatan<br/>• Sub Kegiatan<br/>• Kode Rekening 5.1.02...<br/>• Pagu Volume & Harga Standar"]:::dasar
    end

    subgraph TAHAP1["TAHAP 1: Perencanaan & Surat Pesanan (Sebelum Belanja)"]
        A1["1. Staf Bidang / Pemohon<br/>Input Pengajuan Belanja<br/>(Pilih Item RKA / Barang, Toko, & Volume)"]:::aksi
        A2{"2. Verifikasi PPTK (Kabid)<br/>Kesesuaian Kebutuhan & Sisa Pagu"}:::verif
        A3["Ditolak PPTK<br/>(Revisi / Batal)"]:::tolak
        A4["Status: DISETUJUI PPTK<br/>Cetak SURAT PESANAN (PDF)"]:::dokumen
    end

    subgraph TAHAP2["TAHAP 2: Pembelian, SPJ, & Pencairan Kas (Setelah Belanja)"]
        B1["3. Pembelian Fisik ke Toko<br/>Upload Bukti Faktur/Nota Asli & Foto Fisik"]:::aksi
        B2{"4. Verifikasi PPK (Kasubag Keuangan)<br/>Pemeriksaan Berkas & Kepatuhan Pajak"}:::verif
        B3["Ditolak PPK<br/>(Lengkapi Bukti)"]:::tolak
        B4{"5. Persetujuan PA (Kepala Dinas)<br/>Validasi Pengeluaran Kas Daerah"}:::verif
        B5["Ditolak PA"]:::tolak
        B6["Status: DISETUJUI PA<br/>Terbit DOKUMEN KWITANSI & NPD (PDF)"]:::dokumen
        B7["6. Pencairan oleh Bendahara Pengeluaran<br/>Transfer / Pembayaran Kas Daerah"]:::sukses
        B8["7. Otomatisasi Gudang Persediaan<br/>Barang Masuk (Stock In) & Kartu Stok"]:::sukses
    end

    RKA --> A1
    A1 --> A2
    A2 -- "Tidak Sesuai" --> A3
    A2 -- "Disetujui" --> A4
    A4 --> B1
    B1 --> B2
    B2 -- "Tidak Lengkap" --> B3
    B2 -- "Lengkap" --> B4
    B4 -- "Ditolak" --> B5
    B4 -- "Disetujui" --> B6
    B6 --> B7
    B7 --> B8
```

### Rincian Dokumen & Aktor Keuangan
| Tahapan | Pelaku / Pejabat | Dokumen yang Terbit | Keterangan |
| :--- | :--- | :--- | :--- |
| **Input Pengajuan** | Staf Pemohon | Draft Rincian Belanja | Sistem mengunci input bila volume melebihi sisa pagu RKA. |
| **Persetujuan Pesanan** | Kabid selaku PPTK | **Nota Pesanan Resmi (PDF)** | Menjadi dasar legalitas staf mendatangi toko/penyedia. |
| **Pemeriksaan Berkas** | Kasubag Keu selaku PPK | Lembar Verifikasi SPJ | Memastikan bukti transaksi asli, materai, dan potongan pajak benar. |
| **Persetujuan Belanja** | Kepala Dinas selaku PA | **Kwitansi Pembayaran (PDF)** | Persetujuan tertinggi pengeluaran anggaran dinas. |
| **Pencairan Dana** | Bendahara Pengeluaran | **Nota Pencairan Dana (NPD)** | Kas keluar; item persediaan fisik otomatis tercatat masuk stok. |

---

## 2. Alur Modul Presensi & Kinerja Pegawai (Absensi & LHK)

Presensi pegawai terintegrasi antara **Mesin Biometrik Fingerprint (ZKTeco)** dan **Mobile PWA (GPS Geofencing)**, yang bermuara pada penentuan hak pengisian **Laporan Harian Kinerja (LHK)**.

```mermaid
flowchart TD
    classDef dasar fill:#f8fafc,stroke:#64748b,stroke-width:1.5px,color:#1e293b;
    classDef proses fill:#dbeafe,stroke:#2563eb,stroke-width:2px,color:#1e3a8a;
    classDef keputusan fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#78350f;
    classDef sukses fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#14532d;
    classDef alpa fill:#fee22e,stroke:#dc2626,stroke-width:2px,color:#7f1d1d;
    classDef izin fill:#ede9fe,stroke:#7c3aed,stroke-width:2px,color:#4c1d95;

    subgraph SUMBER["1. Metode Pengambilan Presensi"]
        M1["Mesin Biometrik ZKTeco<br/>(Tarik Log via Socket/IP)"]:::dasar
        M2["Mobile PWA Pegawai<br/>(Validasi Titik GPS & Radius Geofencing)"]:::dasar
        M3["Pengajuan Izin / Sakit / Dinas Luar<br/>(Surat Tugas / Surat Dokter)"]:::izin
    end

    subgraph JENDELA["2. Aturan Waktu Presensi Harian"]
        W1{"06:00 - 09:00 WIB<br/>Jendela Masuk"}:::keputusan
        W2["07:30 - Batas Toleransi<br/>Status: HADIR TEPAT WAKTU"]:::sukses
        W3["> Toleransi s.d 09:00<br/>Status: TERLAMBAT"]:::keputusan
        W4["> 09:00 WIB Tidak Hadir<br/>Status: TIDAK HADIR / ALPA"]:::alpa
    end

    subgraph PULANG["3. Jendela Presensi Pulang"]
        P1{"16:00 / 16:30 - 23:59 WIB<br/>Jendela Pulang"}:::keputusan
        P2["Sudah Tap Masuk Pagi?<br/>(Validasi Prasyarat)"]:::keputusan
        P3["Tap Pulang Diterima<br/>Status: LENGKAP"]:::sukses
        P4["Ditolak Sistem!<br/>Status tetap ALPA"]:::alpa
    end

    subgraph KINERJA["4. Dampak ke Pengisian LHK & Reputasi"]
        K1{"Cek Status Hari Ini"}:::keputusan
        K2["Form LHK TERBUKA<br/>Pegawai Mengisi Rincian Pekerjaan"]:::sukses
        K3["Form LHK TERKUNCI!<br/>(Tidak Berhak Isi LHK)"]:::alpa
        K4["Verifikasi LHK oleh Atasan Langsung<br/>(Disetujui / Perbaikan)"]:::proses
        K5["Penilaian Reputasi Kerja (1 - 5 Bintang)"]:::sukses
    end

    M1 --> W1
    M2 --> W1
    M3 -- "Disetujui Admin/Atasan" --> K2

    W1 -- "Sebelum batas toleransi" --> W2
    W1 -- "Lewat batas toleransi" --> W3
    W1 -- "Tidak ada absen" --> W4

    W2 --> P1
    W3 --> P1
    W4 --> K3

    P1 --> P2
    P2 -- "Ya (Ada Tap Masuk)" --> P3
    P2 -- "Tidak Ada Tap Masuk" --> P4

    P3 --> K1
    P4 --> K3

    K1 -- "Presensi Lengkap / Dinas Luar" --> K2
    K1 -- "Alpa / Belum Pulang" --> K3

    K2 --> K4
    K4 --> K5
```

### Aturan Presensi Khusus
1. **Hari Jumat Otomatis**:
   Sistem otomatis mengenali hari Jumat (`jam_pulang_jumat: 16:30`) tanpa perlu perubahan manual.
2. **Pegawai Dinas Luar (DL)**:
   Surat tugas DL yang disetujui otomatis mencatatkan kehadiran penuh dan membuka akses pengisian LHK.
3. **Pemberian Jam Pulang Massal (1-Klik Admin)**:
   Untuk pegawai yang hadir pagi tetapi lupa tap pulang, admin instansi memiliki tombol bulk action *"Set Jam Pulang Massal"* di tab **Belum Pulang** untuk menyelaraskan status dalam 1 klik.
4. **Pegawai WFH**:
   Pegawai dengan `status_kerja = 'wfh'` dibebaskan dari pembatasan radius geofencing kantor.

---

## 3. Modul Pelaporan Eksekutif: LRFK & Laporan Fungsional Bendahara

Modul ini mengotomatiskan penerbitan laporan pertanggungjawaban fisik dan keuangan daerah sesuai standar Bappeda, Bagian Administrasi Pembangunan, dan BPKAD Kabupaten Sijunjung, tanpa membutuhkan input data manual berulang.

```mermaid
flowchart TD
    classDef input fill:#f8fafc,stroke:#64748b,stroke-width:1.5px,color:#1e293b;
    classDef proses fill:#dbeafe,stroke:#2563eb,stroke-width:2px,color:#1e3a8a;
    classDef kalkulasi fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#78350f;
    classDef output fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#14532d;

    subgraph DATA["1. Sumber Data Transaksional Otomatis"]
        D1["DPA / RKA SIPD<br/>• Pagu Kegiatan & Sub Kegiatan<br/>• Item RKA & Pagu Volume"]:::input
        D2["Transaksi SPJ Dicairkan<br/>• Tanggal Cair & Nomor BKU<br/>• Kuantitas Barang & Subtotal SPJ<br/>• Bukti BAST / Foto Fisik"]:::input
        D3["Target ROPK Bulanan<br/>• Target Kumulatif Fisik %<br/>• Target Anggaran Kas Rp"]:::input
    end

    subgraph ENGINE["2. Otomasi Perhitungan (Kalkulasi Sistem)"]
        E1["Realisasi Keuangan<br/>Akumulasi SPJ Cair per Kegiatan & Rekening"]:::kalkulasi
        E2["Realisasi Fisik Tertimbang<br/>Bobot Item x % Capaian Volume RKA"]:::kalkulasi
        E3["Evaluasi Deviasi & Kinerja<br/>Capaian Fisik % & Serapan Anggaran %"]:::kalkulasi
    end

    subgraph LAPORAN["3. Output Menu Laporan Eksekutif (Cetak PDF)"]
        L1["Laporan Realisasi Fisik & Keuangan (LRFK)<br/>• Matriks Utama LRFK Bulanan<br/>• Lampiran 1: Realisasi Belanja Operasi/Modal<br/>• Kemajuan Pekerjaan Fisik & Kontrak"]:::output
        L2["Laporan Fungsional Bendahara<br/>• Buku Kas Umum (BKU)<br/>• Realisasi Anggaran per Kode Rekening<br/>• Rekapitulasi Pajak & Saldo Kas"]:::output
    end

    D1 --> E1
    D1 --> E2
    D2 --> E1
    D2 --> E2
    D3 --> E3
    E1 --> E3
    E2 --> E3
    E1 --> L1
    E2 --> L1
    E3 --> L1
    E1 --> L2
    D2 --> L2
```

---

### A. Metodologi & Rumus Perhitungan 100% Otomatis

Sistem menghitung seluruh indikator realisasi secara otomatis dari data transaksi SPJ yang telah dicairkan:

#### 1. Perhitungan Realisasi Keuangan
Realisasi keuangan dihitung berdasarkan jumlah rupiah yang benar-benar telah dicairkan oleh Bendahara Pengeluaran dibandingkan dengan total pagu DPA:

$$\text{Realisasi Keuangan (Rp)} = \sum \text{Nilai SPJ Dicairkan s/d Bulan Laporan}$$

$$\text{Realisasi Keuangan (\%)} = \left(\frac{\text{Realisasi Keuangan (Rp)}}{\text{Pagu Anggaran DPA (Rp)}}\right) \times 100\%$$

$$\text{Serapan Anggaran (\%)} = \left(\frac{\text{Realisasi Keuangan (Rp)}}{\text{Target Keuangan ROPK (Rp)}}\right) \times 100\%$$

#### 2. Perhitungan Realisasi Fisik (Metode Pembobotan Proporsional Item)
Realisasi fisik mencerminkan tingkat kemajuan pelaksanaan barang/jasa di lapangan. Sistem menghitung secara otomatis melalui pembobotan item rincian belanja (RKA):

- **Bobot Item Belanja**:
  $$\text{Bobot Item}_i = \left(\frac{\text{Pagu Total Item}_i}{\text{Total Pagu Kegiatan}}\right) \times 100\%$$

- **Progres Fisik Item (% Volume Terealisasi)**:
  Untuk setiap item belanja yang telah diverifikasi fisik dan dicairkan:
  $$\% \text{Progres Item}_i = \min\left(100\%, \frac{\text{Volume SPJ Selesai}_i}{\text{Pagu Volume RKA}_i} \times 100\%\right)$$

- **Realisasi Fisik Tertimbang Sub-Kegiatan / Kegiatan**:
  $$\text{Realisasi Fisik Tertimbang} = \sum_{i=1}^n \left(\text{Bobot Item}_i \times \% \text{Progres Item}_i\right)$$

- **Tingkat Capaian Fisik (%) terhadap ROPK**:
  $$\text{Capaian Fisik (\%)} = \left(\frac{\text{Realisasi Fisik (\%)}}{\text{Target Fisik ROPK (\%)}}\right) \times 100\%$$

---

### B. Struktur Menu Laporan yang Akan Dibangun

Di panel admin Filament, ditambahkan grup navigasi khusus **"Laporan Eksekutif"**:

1. **Menu `Laporan LRFK (Realisasi Fisik & Keuangan)`**:
   - **Filter**: Pilihan Bulan (Januari s.d Desember) & Tahun Anggaran.
   - **Tampilan Tab Interaktif**:
     - **Tab 1: Matriks LRFK Kegiatan**: Tabel komparasi lengkap (Pagu, Target Fisik/Keuangan, Realisasi Fisik/Keuangan, Capaian Fisik %, Serapan Anggaran %).
     - **Tab 2: Lampiran 1 (Jenis Belanja)**: Rekapitulasi per kelompok akun (Belanja Pegawai, Belanja Barang dan Jasa, Belanja Modal Peralatan/Gedung/Jalan).
     - **Tab 3: Kemajuan Fisik Pekerjaan**: Khusus paket kontraktual/belanja modal (Nilai Kontrak, Rekanan, Nomor SPK/Kontrak, Bobot Rencana vs Realisasi, Deviasi).
   - **Aksi Cetak**: Tombol **"Cetak LRFK (PDF Landscape)"** dan **"Ekspor Excel (.xlsx)"** yang menghasilkan format identik dengan dokumen resmi dinas.

2. **Menu `Laporan Pertanggungjawaban Fungsional (LPJ Fungsional)`**:
   - Dikelola oleh **Bendahara Pengeluaran** dan dapat dipantau oleh PPK, PA, dan Auditor.
   - Memuat:
     - Realisasi belanja per Kode Rekening Belanja (DPA, Realisasi s/d Bulan Lalu, Realisasi Bulan Ini, Total s/d Bulan Ini, Sisa Pagu).
     - Register SP2D dan Kas Bank/Tunai.
     - Rekapitulasi Pemotongan dan Penyetoran Pajak (PPN, PPh 21, PPh 22, PPh 23).
   - **Aksi Cetak**: Cetak Buku Kas Umum (BKU) dan LPJ Fungsional format resmi Permendagri 77/2020.


