<div class="p-4 pb-24 flex flex-col min-h-screen bg-slate-50" x-data="{
    map: null,
    marker: null,
    circle: null,
    lat: {{ $latitude_kantor }},
    lng: {{ $longitude_kantor }},
    radiusValue: {{ $radius_meter }},
    currentCoordsText: '{{ $latitude_kantor }}, {{ $longitude_kantor }}',
    detectingGps: false,
    saving: false,

    init() {
        this.waitForLeaflet();
        Livewire.on('radius-updated', (data) => {
            this.radiusValue = data.radius;
            if (this.circle) {
                this.circle.setRadius(data.radius);
            }
        });
    },

    waitForLeaflet() {
        let count = 0;
        const timer = setInterval(() => {
            count++;
            if (window.L && typeof window.L.map === 'function') {
                clearInterval(timer);
                this.initLeafletMap();
            } else if (count > 40) {
                clearInterval(timer);
            }
        }, 100);
    },

    initLeafletMap() {
        const el = document.getElementById('mapContainer');
        if (!el || !window.L) return;

        if (this.map) {
            try { this.map.remove(); } catch (e) {}
        }

        const fallback = document.getElementById('mapLoadingFallback');
        if (fallback) fallback.style.display = 'none';

        this.map = L.map('mapContainer', {
            center: [this.lat, this.lng],
            zoom: 17,
            zoomControl: false,
            scrollWheelZoom: false,
        });

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(this.map);

        L.control.zoom({ position: 'bottomright' }).addTo(this.map);

        const customPinIcon = L.divIcon({
            className: 'custom-leaflet-pin',
            html: `
                    <div style='position: relative; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;'>
                        <div style='position: absolute; width: 32px; height: 32px; background: #0284c7; border: 3px solid #ffffff; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;'>
                            <i class='fa-solid fa-building'></i>
                        </div>
                        <div style='position: absolute; bottom: -6px; width: 8px; height: 8px; background: #0284c7; transform: rotate(45deg);'></div>
                    </div>
                `,
            iconSize: [34, 40],
            iconAnchor: [17, 36],
            popupAnchor: [0, -36]
        });

        this.marker = L.marker([this.lat, this.lng], {
            draggable: true,
            icon: customPinIcon
        }).addTo(this.map);

        this.marker.bindPopup('<b>Titik Pusat Kantor</b><br>Geser pin untuk memindahkan titik.').openPopup();

        this.circle = L.circle([this.lat, this.lng], {
            color: '#0284c7',
            fillColor: '#38bdf8',
            fillOpacity: 0.25,
            weight: 2,
            radius: this.radiusValue
        }).addTo(this.map);

        this.marker.on('dragend', (e) => {
            const pos = e.target.getLatLng();
            this.updatePosition(pos.lat, pos.lng);
        });

        this.map.on('click', (e) => {
            this.updatePosition(e.latlng.lat, e.latlng.lng);
        });

        setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 200);
        setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 600);
    },

    updatePosition(newLat, newLng) {
        this.lat = parseFloat(newLat.toFixed(6));
        this.lng = parseFloat(newLng.toFixed(6));
        this.currentCoordsText = this.lat + ', ' + this.lng;

        if (this.marker) this.marker.setLatLng([this.lat, this.lng]);
        if (this.circle) this.circle.setLatLng([this.lat, this.lng]);
        if (this.map) this.map.panTo([this.lat, this.lng]);

        $wire.set('latitude_kantor', this.lat);
        $wire.set('longitude_kantor', this.lng);
    },

    updateRadius(val) {
        this.radiusValue = parseInt(val);
        if (this.circle) this.circle.setRadius(this.radiusValue);
        $wire.set('radius_meter', this.radiusValue);
    },

    onRadiusSliderChange() {
        const r = parseInt(this.radiusValue);
        if (this.circle) this.circle.setRadius(r);
        $wire.set('radius_meter', r);
    },

    detectMyGps() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung deteksi lokasi Geolocation.');
            return;
        }

        this.detectingGps = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.detectingGps = false;
                this.updatePosition(pos.coords.latitude, pos.coords.longitude);
                if (this.map) {
                    this.map.setView([this.lat, this.lng], 18);
                    this.map.invalidateSize();
                }
            },
            (err) => {
                this.detectingGps = false;
                alert('Gagal mendeteksi GPS perangkat: ' + err.message);
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    },

    saveLocationSettings() {
        this.saving = true;
        $wire.simpanLokasi().then(() => {
            this.saving = false;
        }).catch(() => {
            this.saving = false;
        });
    }
}">
    <!-- Top Navigation -->
    <div class="flex items-center justify-between mb-3.5 pt-1">
        <a href="{{ route('mobile.absensi') }}" wire:navigate
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white text-slate-700 border border-slate-200/90 shadow-xs text-xs font-bold transition-transform duration-150 ease-out active:scale-[0.96]">
            <i class="fa-solid fa-arrow-left text-[11px] text-sky-600"></i>
            <span>Kembali ke Presensi</span>
        </a>
        <span
            class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 shadow-xs flex items-center">
            <i class="fa-solid fa-shield-halved mr-1 text-amber-600"></i>
            <span>Khusus Admin</span>
        </span>
    </div>

    <!-- Page Title & Guidance -->
    <div class="mb-3">
        <h1 class="text-lg font-black text-slate-800 tracking-tight leading-tight">Pengaturan Lokasi & Geofence</h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Geser pin pada peta atau klik tombol GPS untuk
            mengkalibrasi titik pusat presensi kantor.</p>
    </div>

    <!-- MAP CONTAINER CARD -->
    <div class="bg-white rounded-3xl p-3 shadow-sm border border-slate-200/90 mb-4 relative overflow-hidden">
        <!-- Floating GPS & Titik Asli Action Button -->
        <div class="absolute top-5 right-5 z-[500] flex items-center gap-1.5">
            <button type="button" wire:click="kembalikanTitikAsli"
                class="px-2.5 py-1.5 rounded-xl bg-white/95 backdrop-blur-md text-slate-700 hover:text-slate-900 border border-slate-200 shadow-md font-extrabold text-xs flex items-center gap-1 active:scale-95 transition cursor-pointer"
                title="Kembalikan ke titik koordinat resmi kantor">
                <i class="fa-solid fa-rotate-left text-slate-500"></i>
                <span>Titik Asli</span>
            </button>
            <button type="button" @click="detectMyGps()" :disabled="detectingGps"
                class="px-3 py-1.5 rounded-xl bg-white/95 backdrop-blur-md text-sky-700 hover:text-sky-800 border border-slate-200 shadow-md font-extrabold text-xs flex items-center gap-1.5 transition-transform duration-150 ease-out active:scale-[0.96] disabled:scale-100 cursor-pointer">
                <i class="fa-solid fa-location-crosshairs text-sky-600" :class="detectingGps ? 'animate-spin' : ''"></i>
                <span x-text="detectingGps ? 'Membaca GPS...' : 'Gunakan Posisi Saya'">Gunakan Posisi Saya</span>
            </button>
        </div>

        <!-- The Leaflet Map Element with Explicit Height & Isolation -->
        <div id="mapContainer" style="height: 280px; width: 100%; min-height: 280px; position: relative; z-index: 10;"
            class="w-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-100" wire:ignore>
            <!-- Loading Indicator Placeholder -->
            <div id="mapLoadingFallback"
                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 bg-slate-100/90 z-0">
                <i class="fa-solid fa-map-location-dot fa-bounce text-3xl text-sky-500 mb-2"></i>
                <span class="text-xs font-bold text-slate-600">Memuat Peta Satelit / OpenStreetMap...</span>
            </div>
        </div>

        <!-- Coordinates & Live Status Strip -->
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
            <div class="flex items-center space-x-1.5 text-slate-600 min-w-0 pr-2">
                <i class="fa-solid fa-map-pin text-rose-500 text-xs shrink-0"></i>
                <span class="font-bold text-slate-700 font-mono truncate"
                    x-text="currentCoordsText">{{ $latitude_kantor }}, {{ $longitude_kantor }}</span>
            </div>
            <div class="flex items-center space-x-1 text-sky-700 font-bold shrink-0">
                <i class="fa-solid fa-circle-dot text-[10px] text-sky-500"></i>
                <span>Radius <span x-text="radiusValue">{{ $radius_meter }}</span> m</span>
            </div>
        </div>
    </div>

    <!-- CONFIGURATION FORM -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200/90 space-y-4">
        <!-- Quick Radius Selector -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <i class="fa-solid fa-bullseye text-sky-600"></i>
                    <span>Radius Geofencing Presensi</span>
                </label>
                <span class="text-xs font-black font-mono text-sky-700"
                    x-text="radiusValue + ' Meter'">{{ $radius_meter }} Meter</span>
            </div>

            <div class="grid grid-cols-5 gap-1.5 mb-2.5">
                <template x-for="r in [5, 10, 15, 25, 50]" :key="r">
                    <button type="button" @click="updateRadius(r)"
                        class="py-1.5 rounded-xl text-xs font-extrabold border transition-transform duration-150 ease-out active:scale-[0.96] cursor-pointer text-center"
                        :class="Number(radiusValue) === r ?
                            'bg-sky-600 text-white border-sky-600 shadow-xs' :
                            'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                        <span x-text="r + 'm'"></span>
                    </button>
                </template>
            </div>

            <!-- Custom Radius Slider & Input Manual -->
            <div class="flex items-center gap-2.5">
                <input type="range" min="1" max="200" step="1" x-model.number="radiusValue"
                    @input="onRadiusSliderChange()" class="flex-1 accent-sky-600 cursor-pointer">
                <div class="flex items-center gap-1 shrink-0">
                    <input type="number" min="1" max="5000" x-model.number="radiusValue"
                        @input="onRadiusSliderChange()"
                        class="w-16 px-2 py-1 text-xs font-bold font-mono text-center bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-sky-500">
                    <span class="text-[11px] font-bold text-slate-500">m</span>
                </div>
            </div>
        </div>

        <!-- Alamat Kantor Resmi -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-building text-sky-600"></i>
                <span>Nama Gedung / Alamat Kantor</span>
            </label>
            <input type="text" wire:model="alamat_kantor"
                class="w-full px-3 py-2 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                placeholder="Gedung Bersama Lt. III, Jl. Prof. M. Yamin...">
            @error('alamat_kantor')
                <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span>
            @enderror
        </div>

        <!-- Mode Simulasi Bypass Geofence Toggle -->
        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/90 flex items-center justify-between">
            <div class="pr-3">
                <span class="text-xs font-extrabold text-slate-800 block">Mode Simulasi Bebas Lokasi</span>
                <span class="text-[10px] text-slate-500 block leading-tight mt-0.5">Izinkan presensi tanpa batasan
                    radius geofencing kantor saat uji coba admin.</span>
            </div>
            <button type="button" wire:click="toggleSimulasi"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $mode_simulasi ? 'bg-sky-600' : 'bg-slate-300' }}">
                <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ $mode_simulasi ? 'translate-x-5' : 'translate-x-0' }}"></span>
            </button>
        </div>

        @if ($mode_simulasi)
            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <div class="text-xs font-extrabold text-amber-800">Simulasi Jam Aktif Server</div>
                        <div class="text-[10px] text-amber-700/80">Kosongkan untuk memakai jam server asli.</div>
                    </div>
                    <input type="time" wire:model="simulasi_jam_aktif"
                        class="rounded-xl border border-amber-300 bg-white px-3 py-1.5 text-xs font-mono font-bold text-slate-800">
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" wire:click="$set('simulasi_jam_aktif', '07:25')" class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-[10px] font-bold text-amber-800 hover:bg-amber-100">07:25 Masuk</button>
                    <button type="button" wire:click="$set('simulasi_jam_aktif', '08:05')" class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-[10px] font-bold text-amber-800 hover:bg-amber-100">08:05 Terlambat</button>
                    <button type="button" wire:click="$set('simulasi_jam_aktif', '16:05')" class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-[10px] font-bold text-amber-800 hover:bg-amber-100">16:05 Pulang</button>
                    <button type="button" wire:click="$set('simulasi_jam_aktif', null)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-300 text-[10px] font-bold text-slate-600 hover:bg-slate-100">Reset Real-Time</button>
                </div>
            </div>
        @endif

        <!-- Tombol Simpan -->
        <div class="pt-2">
            <button type="button" @click="saveLocationSettings()" :disabled="saving"
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-500 hover:to-sky-600 text-white font-extrabold text-xs shadow-lg shadow-sky-600/25 enabled:active:scale-[0.96] transition-transform duration-150 ease-out disabled:scale-100 flex items-center justify-center space-x-2 cursor-pointer">
                <template x-if="saving">
                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                </template>
                <template x-if="!saving">
                    <i class="fa-solid fa-floppy-disk text-xs text-sky-200"></i>
                </template>
                <span x-text="saving ? 'Menyimpan...' : 'Simpan Titik & Radius Kantor'">Simpan Titik & Radius
                    Kantor</span>
            </button>
        </div>
    </div>
</div>
