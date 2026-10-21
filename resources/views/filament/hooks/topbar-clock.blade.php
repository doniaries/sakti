<div
    x-data="{
        time: '',
        date: '',
        updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const dayNum = String(now.getDate()).padStart(2, '0');
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            this.date = `${dayName}, ${dayNum} ${monthName} ${year}`;
            this.time = `${hours}:${minutes}:${seconds} WIB`;
        }
    }"
    x-init="updateClock(); setInterval(() => updateClock(), 1000)"
    style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; justify-content: center !important; flex-wrap: nowrap !important; white-space: nowrap !important; background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important; color: #ffffff !important; border: none !important; outline: none !important; box-shadow: 0 2px 8px rgba(0,0,0,0.25) !important; padding: 6px 18px !important; border-radius: 9999px !important; font-size: 13px !important; font-weight: 600 !important; gap: 12px !important; margin-right: 12px !important; user-select: none !important;"
>
    <!-- Hari & Tanggal -->
    <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; gap: 6px !important; color: #ffffff !important; flex-shrink: 0 !important; white-space: nowrap !important;">
        <svg style="width: 16px; height: 16px; flex-shrink: 0; color: #fef08a;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
        </svg>
        <span x-text="date" style="color: #ffffff; letter-spacing: 0.02em; font-weight: 600;">{{ now()->translatedFormat('l, d F Y') }}</span>
    </div>

    <!-- Separator -->
    <span style="color: #fef08a; font-weight: 700; opacity: 0.85; user-select: none; line-height: 1;">•</span>

    <!-- Jam Berjalan Realtime -->
    <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; gap: 6px !important; color: #ffffff !important; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important; font-weight: 700 !important; letter-spacing: 0.05em !important; flex-shrink: 0 !important; white-space: nowrap !important;">
        <svg style="width: 16px; height: 16px; flex-shrink: 0; color: #fef08a;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
        </svg>
        <span x-text="time" style="color: #ffffff; font-weight: 700;">{{ now()->format('H:i:s') }} WIB</span>
    </div>
</div>
