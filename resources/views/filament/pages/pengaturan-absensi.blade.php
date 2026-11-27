<x-filament-panels::page>
    <div class="space-y-6" x-data="{
        lat: @entangle('latitude_kantor'),
        lng: @entangle('longitude_kantor'),
        radius: @entangle('radius_meter'),
        map: null,
        marker: null,
        circle: null,
        detecting: false,

        init() {
            this.loadLeaflet();
            $wire.on('lokasi-diperbarui', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                this.updateMapPosition(data.lat, data.lng, data.radius);
            });
        },

        loadLeaflet() {
            if (window.L && typeof window.L.map === 'function') {
                this.initMap();
                return;
            }

            if (!document.getElementById('leaflet-css')) {
                const link = document.createElement('link');
                link.id = 'leaflet-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }

            if (!document.getElementById('leaflet-js')) {
                const script = document.createElement('script');
                script.id = 'leaflet-js';
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => {
                    setTimeout(() => this.initMap(), 150);
                };
                document.body.appendChild(script);
            } else {
                let interval = setInterval(() => {
                    if (window.L) {
                        clearInterval(interval);
                        this.initMap();
                    }
                }, 100);
            }
        },

        initMap() {
            const container = document.getElementById('filamentMapContainer');
            if (!container || !window.L) return;

            if (this.map) {
                try { this.map.remove(); } catch (e) {}
            }

            this.map = L.map('filamentMapContainer', {
                center: [this.lat, this.lng],
                zoom: 17,
                scrollWheelZoom: true,
            });

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            const pinIcon = L.divIcon({
                className: 'custom-pin',
                html: `<div style='width: 32px; height: 32px; background: #0284c7; border: 3px solid white; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white;'>
                    <i class='fa-solid fa-building text-xs'></i>
                </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
            });

            this.marker = L.marker([this.lat, this.lng], {
                draggable: true,
                icon: pinIcon
            }).addTo(this.map);

            this.circle = L.circle([this.lat, this.lng], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.25,
                weight: 2,
                radius: this.radius
            }).addTo(this.map);

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.setCoords(pos.lat, pos.lng);
            });

            this.map.on('click', (e) => {
                this.setCoords(e.latlng.lat, e.latlng.lng);
            });

            setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 300);
        },

        setCoords(newLat, newLng) {
            this.lat = parseFloat(newLat.toFixed(6));
            this.lng = parseFloat(newLng.toFixed(6));
            if (this.marker) this.marker.setLatLng([this.lat, this.lng]);
            if (this.circle) this.circle.setLatLng([this.lat, this.lng]);
            $wire.set('latitude_kantor', this.lat);
            $wire.set('longitude_kantor', this.lng);
        },

        updateMapPosition(newLat, newLng, newRadius) {
            this.lat = newLat;
            this.lng = newLng;
            if (newRadius) this.radius = newRadius;
            if (this.marker) this.marker.setLatLng([this.lat, this.lng]);
            if (this.circle) {
                this.circle.setLatLng([this.lat, this.lng]);
                if (newRadius) this.circle.setRadius(this.radius);
            }
            if (this.map) {
                this.map.panTo([this.lat, this.lng]);
            }
        },

        updateRadius(r) {
            this.radius = parseInt(r);
            if (this.circle) this.circle.setRadius(this.radius);
            $wire.set('radius_meter', this.radius);
        },

        ambilGpsLaptop() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung deteksi lokasi Geolocation.');
                return;
            }

            this.detecting = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.detecting = false;
                    const uLat = pos.coords.latitude;
                    const uLng = pos.coords.longitude;
                    this.setCoords(uLat, uLng);
                    if (this.map) {
                        this.map.setView([uLat, uLng], 18);
                    }
                    $wire.setKoordinat(uLat, uLng);
                },
                (err) => {
                    this.detecting = false;
                    alert('Gagal mendeteksi lokasi laptop: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    }">
        <div class="p-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-zinc-800 space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-100 dark:border-zinc-800">
                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-zinc-100">Titik Koordinat & Radius Kantor (Geofence)</h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Tentukan titik koordinat pusat presensi kedinasan dan batas radius toleransi GPS bagi pegawai WFO.
                        </p>
                    </div>
                </div>

                <div class="flex items-center flex-wrap gap-2">
                    <button type="button" @click="ambilGpsLaptop()" :disabled="detecting"
                        class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm active:scale-95 transition disabled:opacity-50 cursor-pointer">
                        <i class="fa-solid fa-crosshairs" :class="detecting ? 'animate-spin' : ''"></i>
                        <span x-text="detecting ? 'Membaca GPS...' : 'Ambil Titik Lokasi Laptop (GPS)'">Ambil Titik Lokasi Laptop (GPS)</span>
                    </button>

                    <button type="button" wire:click="kembalikanKeTitikAsli"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 font-bold text-xs flex items-center gap-1.5 active:scale-95 transition cursor-pointer"
                        title="Kembalikan koordinat ke titik default resmi kantor">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Kembalikan ke Titik Asli</span>
                    </button>
                </div>
            </div>

            <!-- Formulir Koordinat & Radius -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5">
                        Latitude Kantor <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="any" wire:model.live.debounce.500ms="latitude_kantor" @change="updateMapPosition($event.target.value, lng, radius)" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 font-mono font-bold text-xs focus:ring-2 focus:ring-sky-500/30">
                    <span class="text-[10px] text-slate-400 mt-1 block">Garis lintang (terisi otomatis dari peta/GPS)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5">
                        Longitude Kantor <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="any" wire:model.live.debounce.500ms="longitude_kantor" @change="updateMapPosition(lat, $event.target.value, radius)" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 font-mono font-bold text-xs focus:ring-2 focus:ring-sky-500/30">
                    <span class="text-[10px] text-slate-400 mt-1 block">Garis bujur (terisi otomatis dari peta/GPS)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5">
                        Radius Presensi (Meter) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" min="10" max="5000" wire:model.live="radius_meter" @input="updateRadius($event.target.value)" required
                            class="w-full pl-3.5 pr-14 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 font-bold text-xs focus:ring-2 focus:ring-sky-500/30">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-bold text-slate-400 pointer-events-none">Meter</span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Batas toleransi radius geofence</span>
                </div>
            </div>

            <!-- Quick Radius Buttons -->
            <div class="flex items-center gap-2 pt-1">
                <span class="text-xs font-bold text-slate-500 mr-2">Pilihan Cepat Radius:</span>
                @foreach([25, 50, 100, 150, 200] as $r)
                    <button type="button" @click="updateRadius({{ $r }})"
                        :class="radius == {{ $r }} ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-200'"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer">
                        {{ $r }}m
                    </button>
                @endforeach
            </div>

            <!-- Mode Simulasi Bebas Lokasi Toggle -->
            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-400/30 flex items-center justify-between">
                <div class="pr-4">
                    <span class="text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-flask"></i> Mode Simulasi Bebas Lokasi
                    </span>
                    <p class="text-[11px] text-amber-700/80 dark:text-amber-400/80 mt-0.5">
                        Jika diaktifkan, seluruh pegawai dapat melakukan presensi tanpa terblokir radius geofencing kantor untuk kebutuhan uji coba sistem.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" wire:model.live="mode_simulasi" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            @if ($mode_simulasi)
                <div class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 space-y-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="text-xs font-bold text-amber-800 dark:text-amber-300">Simulasi Jam Aktif Server (Opsional)</div>
                            <div class="text-[11px] text-amber-700/80 dark:text-amber-400/80">Kosongkan untuk memakai jam server asli. Di luar mode simulasi, jam tidak dapat diubah manual.</div>
                        </div>
                        <input type="time" wire:model="simulasi_jam_aktif"
                            class="rounded-lg border border-amber-300 bg-white dark:bg-zinc-900 px-3 py-1.5 text-sm font-mono font-bold text-slate-800 dark:text-white">
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" wire:click="$set('simulasi_jam_aktif', '07:25')" class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-[11px] font-bold text-amber-800 hover:bg-amber-100">07:25 (Masuk)</button>
                        <button type="button" wire:click="$set('simulasi_jam_aktif', '08:05')" class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-[11px] font-bold text-amber-800 hover:bg-amber-100">08:05 (Terlambat)</button>
                        <button type="button" wire:click="$set('simulasi_jam_aktif', '16:05')" class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-[11px] font-bold text-amber-800 hover:bg-amber-100">16:05 (Pulang)</button>
                        <button type="button" wire:click="$set('simulasi_jam_aktif', null)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-300 text-[11px] font-bold text-slate-600 hover:bg-slate-100">Reset Real-Time</button>
                    </div>
                </div>
            @endif

            <!-- Map Leaflet Container -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-2">
                    Peta Interaktif (Geser Pin atau Klik Peta untuk Memindahkan Titik)
                </label>
                <div id="filamentMapContainer" style="height: 420px; width: 100%; min-height: 420px; border-radius: 1rem;"
                    class="w-full rounded-2xl overflow-hidden border border-slate-300 dark:border-zinc-700 bg-slate-100 z-0" wire:ignore>
                </div>
            </div>

            <!-- Alamat Kantor -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider mb-1.5">
                    Nama Gedung / Alamat Kantor
                </label>
                <input type="text" wire:model="alamat_kantor" placeholder="Contoh: Gedung Bersama Lt. III, Jl. Prof. M. Yamin, SH No. 1, Muaro Sijunjung"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 text-xs">
            </div>

            <!-- Pengaturan Jam Kerja & Jendela Waktu Presensi (Jadwal Default) -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200 dark:border-zinc-700 space-y-4">
                <div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-zinc-100">Pengaturan Jam Masuk &amp; Pulang (Jadwal Default)</h4>
                    <p class="text-[11px] text-slate-500 dark:text-zinc-400">Sinkron langsung dengan Jadwal Kerja default pada Filament.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Buka Absen Masuk</label>
                        <input type="time" wire:model="buka_absen_masuk"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Jam Masuk (Senin - Kamis)</label>
                        <input type="time" wire:model="jam_masuk_senin_kamis"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Jam Masuk (Jumat)</label>
                        <input type="time" wire:model="jam_masuk_jumat"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Tutup Absen Masuk</label>
                        <input type="time" wire:model="tutup_absen_masuk"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Toleransi Terlambat (Menit)</label>
                        <input type="number" min="0" max="180" wire:model="toleransi_terlambat_menit"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Jam Pulang (Senin - Kamis)</label>
                        <input type="time" wire:model="jam_pulang_senin_kamis"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Jam Pulang (Jumat)</label>
                        <input type="time" wire:model="jam_pulang_jumat"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-zinc-300 uppercase mb-1">Tutup Absen Pulang</label>
                        <input type="time" wire:model="tutup_absen_pulang"
                            class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-slate-300 dark:border-zinc-700 rounded-xl text-xs font-mono font-bold text-slate-800 dark:text-zinc-100">
                    </div>
                </div>
            </div>

            <!-- Save Action -->
            <div class="pt-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                <span class="text-xs text-slate-400 font-mono">
                    Koordinat aktif: <strong class="text-sky-600 dark:text-sky-400" x-text="lat + ', ' + lng">{{ $latitude_kantor }}, {{ $longitude_kantor }}</strong>
                </span>
                <button type="button" wire:click="simpan" wire:loading.attr="disabled"
                    class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs shadow-md shadow-sky-600/20 active:scale-95 transition-all flex items-center space-x-2 cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove><i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Pengaturan Absensi</span>
                    <span wire:loading><i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>

