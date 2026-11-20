@php
    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();
    $unreadCount = $authUser ? $authUser->unreadNotifications()->count() : 0;
    $isAdminUser = $authUser && $authUser->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'pa', 'ppk', 'pptk', 'bendahara']);
@endphp

<header x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        unreadCount: {{ (int) $unreadCount }},
        toggleTheme() {
            if (window.toggleMobileTheme) {
                window.toggleMobileTheme();
                this.isDark = document.documentElement.classList.contains('dark');
            }
        }
    }"
    @mobile-theme-changed.window="isDark = $event.detail.dark"
    @notif-count-updated.window="unreadCount = $event.detail.count ?? 0"
    class="sticky top-0 z-30 relative overflow-hidden rounded-b-[28px] bg-gradient-to-br from-sky-600 via-blue-600 to-teal-500 dark:from-slate-900 dark:via-sky-950 dark:to-teal-950 text-white shadow-lg shadow-sky-900/15 dark:shadow-black/40 border-b border-white/15 dark:border-sky-400/15 px-4 pt-3.5 pb-4 transition-colors duration-300">

    <!-- Subtle Ambient Glow & Curved Accent -->
    <div class="pointer-events-none absolute -top-10 -right-10 w-32 h-32 rounded-full bg-white/15 dark:bg-sky-400/15 blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-10 -left-8 w-28 h-28 rounded-full bg-teal-300/20 dark:bg-emerald-400/10 blur-2xl"></div>

    <div class="relative z-10 flex items-center justify-between gap-2">
        <!-- Brand -->
        <a href="{{ route('mobile.dashboard') }}" wire:navigate class="flex items-center gap-2.5 min-w-0 group">
            <div class="w-9 h-9 rounded-2xl bg-white/20 dark:bg-white/10 border border-white/25 backdrop-blur-md p-1.5 flex items-center justify-center shadow-sm shrink-0 group-active:scale-95 transition-transform">
                <img src="{{ asset('favicon.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div class="min-w-0">
                <div class="text-xs font-black tracking-tight text-white truncate">SAKTI PARPORA</div>
                <div class="text-[10px] text-sky-100 dark:text-sky-300 font-bold uppercase tracking-wider truncate">
                    Presensi &amp; Kinerja
                </div>
            </div>
        </a>

        <!-- Action Buttons -->
        <div class="flex items-center gap-1.5 shrink-0">
            <!-- Notification Bell with Badge -->
            <button type="button"
                @click="window.dispatchEvent(new CustomEvent('open-notif-sheet'))"
                title="Notifikasi"
                class="relative w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 dark:bg-white/10 dark:hover:bg-white/15 border border-white/20 text-white flex items-center justify-center active:scale-90 transition-all cursor-pointer backdrop-blur-md">
                <i class="fa-regular fa-bell text-xs"></i>
                <span x-show="unreadCount > 0" x-cloak
                    x-text="unreadCount > 9 ? '9+' : unreadCount"
                    class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-500 text-white text-[9px] font-extrabold flex items-center justify-center shadow">
                </span>
            </button>

            <!-- Icon Morph Theme Toggle -->
            <button type="button" @click="toggleTheme()" title="Ganti Mode Terang / Gelap"
                class="w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 dark:bg-white/10 dark:hover:bg-white/15 border border-white/20 text-white flex items-center justify-center active:scale-90 transition-all cursor-pointer backdrop-blur-md">
                <i x-show="!isDark" class="fa-solid fa-moon text-xs"></i>
                <i x-show="isDark" x-cloak class="fa-solid fa-sun text-amber-300 text-xs"></i>
            </button>

            @if ($isAdminUser)
                <a href="{{ url('/admin') }}" title="Panel Admin"
                    class="w-9 h-9 rounded-full bg-amber-400/25 hover:bg-amber-400/35 border border-amber-300/40 text-amber-200 flex items-center justify-center active:scale-90 transition-all backdrop-blur-md">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                </a>
            @endif

            <a href="{{ route('portal') }}" title="Portal Beranda"
                class="w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 dark:bg-white/10 dark:hover:bg-white/15 border border-white/20 text-white flex items-center justify-center active:scale-90 transition-all backdrop-blur-md">
                <i class="fa-solid fa-house text-xs"></i>
            </a>

            <form action="{{ route('portal.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Keluar"
                    class="w-9 h-9 rounded-full bg-rose-500/25 hover:bg-rose-500/40 border border-rose-300/35 text-rose-100 flex items-center justify-center active:scale-90 transition-all cursor-pointer backdrop-blur-md">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</header>
