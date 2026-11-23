// ==UserScript==
// @name         SAKTI -> Presency Sijunjung Auto-Sync
// @namespace    https://absensi.sijunjung.go.id/
// @version      1.0.0
// @description  Sinkronisasi data absensi dari file JSON export SAKTI ke halaman ambil-absen.asp Presency Kominfo Sijunjung.
// @match        https://absensi.sijunjung.go.id/ambil-absen.asp*
// @grant        none
// ==/UserScript==

(function () {
    'use strict';

    const DELAY_MS = 400;
    let isRunning = false;
    let shouldStop = false;
    let loadedRecords = [];

    const normalizeNip = (val) => String(val || '').replace(/[\s-]+/g, '').trim();
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    function createPanel() {
        if (document.getElementById('sakti-sync-panel')) return;

        const panel = document.createElement('div');
        panel.id = 'sakti-sync-panel';
        panel.style.cssText = [
            'position:fixed',
            'right:16px',
            'bottom:16px',
            'width:360px',
            'z-index:99999',
            'background:#ffffff',
            'border:1px solid #d1d5db',
            'border-radius:10px',
            'box-shadow:0 10px 25px rgba(0,0,0,0.18)',
            'padding:14px',
            'font-family:Poppins,sans-serif',
            'font-size:12px',
            'color:#1f2937',
        ].join(';');

        panel.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <strong style="font-size:13px;color:#4f46e5;">SAKTI → Presency Sync</strong>
                <button type="button" id="sakti-toggle-btn" style="border:none;background:#f3f4f6;border-radius:4px;padding:2px 8px;cursor:pointer;">_</button>
            </div>
            <div id="sakti-panel-body">
                <div style="margin-bottom:8px;">
                    <label style="display:block;font-weight:600;margin-bottom:4px;">1. Pilih File JSON dari SAKTI</label>
                    <input type="file" id="sakti-json-file" accept=".json,application/json" style="width:100%;font-size:11px;" />
                    <div id="sakti-file-info" style="margin-top:4px;color:#6b7280;">Belum ada file dipilih.</div>
                </div>
                <div style="margin-bottom:8px;">
                    <label style="display:block;font-weight:600;margin-bottom:4px;">2. Filter 1 NIP (Opsional untuk Uji Coba)</label>
                    <input type="text" id="sakti-filter-nip" placeholder="Contoh: 198204142002121003 (kosong = semua)" style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:11px;" />
                </div>
                <div style="margin-bottom:8px;display:flex;flex-direction:column;gap:4px;">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" id="sakti-dry-run" checked />
                        <span><b>Mode Simulasi (Dry Run)</b> — tidak menyimpan ke server</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" id="sakti-only-empty" checked />
                        <span>Hanya isi sel yang masih kosong</span>
                    </label>
                </div>
                <div style="display:flex;gap:6px;margin-bottom:8px;">
                    <button type="button" id="sakti-start-btn" disabled style="flex:1;background:#4f46e5;color:#fff;border:none;border-radius:6px;padding:7px;font-weight:600;cursor:pointer;opacity:0.6;">Mulai Sync</button>
                    <button type="button" id="sakti-stop-btn" disabled style="background:#ef4444;color:#fff;border:none;border-radius:6px;padding:7px 12px;font-weight:600;cursor:pointer;opacity:0.6;">Stop</button>
                </div>
                <div id="sakti-log" style="max-height:160px;overflow-y:auto;background:#111827;color:#e5e7eb;padding:8px;border-radius:6px;font-family:monospace;font-size:11px;line-height:1.4;">Siap. Pilih file JSON export SAKTI terlebih dahulu.</div>
            </div>
        `;

        document.body.appendChild(panel);

        const bodyEl = document.getElementById('sakti-panel-body');
        document.getElementById('sakti-toggle-btn').addEventListener('click', () => {
            bodyEl.style.display = bodyEl.style.display === 'none' ? 'block' : 'none';
        });

        document.getElementById('sakti-json-file').addEventListener('change', handleFileSelect);
        document.getElementById('sakti-start-btn').addEventListener('click', startSync);
        document.getElementById('sakti-stop-btn').addEventListener('click', () => {
            shouldStop = true;
            log('Menghentikan proses setelah item saat ini...');
        });
    }

    function log(msg) {
        const logEl = document.getElementById('sakti-log');
        if (!logEl) return;
        const time = new Date().toLocaleTimeString('id-ID', { hour12: false });
        logEl.innerHTML += `<div>[${time}] ${msg}</div>`;
        logEl.scrollTop = logEl.scrollHeight;
    }

    function handleFileSelect(e) {
        const file = e.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = () => {
            try {
                const parsed = JSON.parse(String(reader.result || '{}'));
                loadedRecords = Array.isArray(parsed) ? parsed : (parsed.records || []);
                document.getElementById('sakti-file-info').textContent = `${loadedRecords.length} record dimuat (Periode: ${parsed.periode || '-'})`;
                const startBtn = document.getElementById('sakti-start-btn');
                startBtn.disabled = loadedRecords.length === 0;
                startBtn.style.opacity = loadedRecords.length === 0 ? '0.6' : '1';
                log(`File dimuat: ${loadedRecords.length} record.`);
            } catch (err) {
                alert('File JSON tidak valid: ' + err.message);
            }
        };
        reader.readAsText(file);
    }

    function buildCellIndex() {
        const map = new Map();
        const rows = document.querySelectorAll('table.table tbody tr');
        rows.forEach((tr) => {
            const tds = tr.querySelectorAll('td');
            if (tds.length < 3) return;
            const nip = normalizeNip(tds[1].textContent);
            if (!nip) return;

            tr.querySelectorAll('td.modal-ambil-absen').forEach((td) => {
                const tanggal = td.getAttribute('tanggal');
                if (tanggal) {
                    map.set(`${nip}|${tanggal}`, td);
                }
            });
        });
        return map;
    }

    async function postForm(url, paramsObj, expectJson = false) {
        const body = new URLSearchParams();
        Object.entries(paramsObj).forEach(([k, v]) => {
            if (v !== undefined && v !== null) body.append(k, String(v));
        });

        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
        });

        if (!res.ok) throw new Error(`HTTP ${res.status} pada ${url}`);
        return expectJson ? res.json() : res.text();
    }

    async function syncOneCell(td, record, isDryRun) {
        const id = td.getAttribute('id');
        const ktg = td.getAttribute('ktg');
        const tanggal = td.getAttribute('tanggal');
        const bulan = td.getAttribute('bulan');
        const tahun = td.getAttribute('tahun');

        if (isDryRun) {
            log(`[SIMULASI] ${record.nama} (${tanggal}) -> keterangan_id=${record.keterangan_id} (${record.keterangan_label}), masuk=${record.jam_masuk}`);
            return;
        }

        // 1. Ambil struktur form modal dari server
        const modalHtml = await postForm('pages/ambilabsen/modal-ambil-absen.php', { id, ktg, tanggal, bulan, tahun }, false);
        const doc = new DOMParser().parseFromString(modalHtml, 'text/html');
        const form = doc.querySelector('form#proses-ambil-absen');
        if (!form) throw new Error('Form #proses-ambil-absen tidak ditemukan di modal');

        const payload = {};
        form.querySelectorAll('input, select').forEach((el) => {
            if (el.name) payload[el.name] = el.value;
        });

        // 2. Hitung keterangan
        const ketData = await postForm('pages/ambilabsen/hitungketerangan.php', { keterangan: record.keterangan_id }, true);
        payload.keterangan_id = record.keterangan_id;
        payload.potongan_kehadiran = ketData.potongan ?? payload.potongan_kehadiran ?? '0';
        payload.BALANCE = ketData.BALANCE ?? payload.BALANCE ?? '0';
        payload.terdaftar = ketData.keterangan_name ?? payload.terdaftar ?? '';
        payload.CUTI = ketData.CUTI ?? payload.CUTI ?? '0';

        // Parse opsi_kegiatan_id jika dikirim sebagai HTML snippet (bisa <input> atau <select>)
        if (ketData.kegiatan) {
            const kegDoc = new DOMParser().parseFromString(ketData.kegiatan, 'text/html');
            const opsiEl = kegDoc.querySelector('[name="opsi_kegiatan_id"]');
            if (opsiEl) {
                if (opsiEl.tagName === 'SELECT') {
                    const opts = Array.from(opsiEl.querySelectorAll('option'));
                    const mengikutiOpt = opts.find(
                        (o) => o.value && /mengikuti/i.test(o.textContent) && !/tidak/i.test(o.textContent)
                    ) || opts.find((o) => o.value && o.value !== '0');
                    if (mengikutiOpt) payload.opsi_kegiatan_id = mengikutiOpt.value;
                } else {
                    payload.opsi_kegiatan_id = opsiEl.value;
                }
            }
            const kegInput = kegDoc.querySelector('[name="kegiatan"]');
            if (kegInput) payload.kegiatan = kegInput.value;
        }

        // 3. Hitung terlambat jika Hadir (1)
        const jamMasuk = record.keterangan_id === '1' ? (record.jam_masuk || '07:30:00') : '00:00:00';
        payload.masuk = jamMasuk;
        if (record.keterangan_id === '1') {
            const telatData = await postForm('pages/ambilabsen/hitungterlambat.php', {
                tanggal: payload.tanggal || tanggal,
                jammasuk: payload.jammasuk || '07:30:00',
                masuk: jamMasuk,
            }, true);
            payload.potongan_terlambat = telatData.id ?? '0';
        } else {
            payload.potongan_terlambat = '0';
        }

        // Pastikan field numerik tidak kosong (mencegah HTTP 500 di PHP/MySQL strict mode)
        if (!payload.potongan_apel) payload.potongan_apel = '0';

        // 4. Submit simpan
        const saveRes = await postForm('pages/ambilabsen/proses-ambil-absen.php', payload, true);
        if (String(saveRes.id) === '0') {
            throw new Error(saveRes.komen || 'Gagal disimpan oleh server');
        }

        td.innerHTML = saveRes.keterangan || record.keterangan_label;
        log(`[OK] ${record.nama} (${tanggal}) -> ${saveRes.keterangan || record.keterangan_label}`);
    }

    async function startSync() {
        if (isRunning) return;
        isRunning = true;
        shouldStop = false;

        const filterNip = normalizeNip(document.getElementById('sakti-filter-nip').value);
        const isDryRun = document.getElementById('sakti-dry-run').checked;
        const onlyEmpty = document.getElementById('sakti-only-empty').checked;

        const startBtn = document.getElementById('sakti-start-btn');
        const stopBtn = document.getElementById('sakti-stop-btn');
        startBtn.disabled = true;
        stopBtn.disabled = false;
        stopBtn.style.opacity = '1';

        try {
            const cellIndex = buildCellIndex();
            if (cellIndex.size === 0) {
                log('Tabel absensi belum dimuat! Pilih Tahun & Bulan lalu klik Masuk dulu.');
                return;
            }

            const targets = loadedRecords.filter((rec) => {
                if (filterNip && normalizeNip(rec.nip) !== filterNip) return false;
                return true;
            });

            log(`Memulai (${isDryRun ? 'SIMULASI' : 'LIVE'}) untuk ${targets.length} record...`);
            let processed = 0;
            let skipped = 0;
            let failed = 0;

            for (const rec of targets) {
                if (shouldStop) break;
                const key = `${normalizeNip(rec.nip)}|${rec.tanggal}`;
                const td = cellIndex.get(key);

                if (!td) {
                    skipped++;
                    continue;
                }

                const currentText = td.textContent.trim();
                if (onlyEmpty && currentText !== '') {
                    skipped++;
                    continue;
                }

                try {
                    await syncOneCell(td, rec, isDryRun);
                    processed++;
                } catch (err) {
                    failed++;
                    log(`[GAGAL] ${rec.nama} (${rec.tanggal}): ${err.message}`);
                }

                await sleep(isDryRun ? 30 : DELAY_MS);
            }

            log(`Selesai. Diproses: ${processed}, Dilewati: ${skipped}, Gagal: ${failed}.`);
        } finally {
            isRunning = false;
            startBtn.disabled = false;
            stopBtn.disabled = true;
            stopBtn.style.opacity = '0.6';
        }
    }

    window.addEventListener('load', createPanel);
    if (document.readyState === 'complete') createPanel();
})();
