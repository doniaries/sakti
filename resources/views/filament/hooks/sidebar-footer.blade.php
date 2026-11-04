@php
    /** @var \App\Models\User|null $user */
    $user = filament()->auth()->user();
    $editProfileUrl = \Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage::getUrl();
    $logoutUrl = filament()->getLogoutUrl();
    $isEditProfileActive = request()->url() === $editProfileUrl;
@endphp

@if ($user)
<div
    x-data="{ open: false }"
    @click.outside="open = false"
    class="fi-sidebar-footer border-t border-gray-200/80 dark:border-white/10 p-2 bg-gray-50/70 dark:bg-gray-900/60 relative"
>
    {{-- Tampilan saat sidebar terbuka (Desktop & Mobile) --}}
    <div x-show="$store.sidebar.isOpen" class="space-y-1.5">

        {{-- Dropdown Menu Akun (Collapsible / Default Tertutup agar Tidak Salah Tekan) --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
            class="p-2 space-y-1 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 shadow-xl mb-1.5"
        >
            <div class="px-2 py-1 border-b border-gray-100 dark:border-white/5 mb-1">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">
                    Pengaturan Akun
                </p>
            </div>

            {{-- Tombol Edit Profil --}}
            <a
                href="{{ $editProfileUrl }}"
                wire:navigate
                @click="open = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors {{ $isEditProfileActive ? 'bg-primary-500 text-white shadow-xs' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/10' }}"
            >
                <x-heroicon-m-user-circle class="w-4 h-4 shrink-0 {{ $isEditProfileActive ? 'text-white' : 'text-primary-500' }}" />
                <span>Edit Profil</span>
            </a>

            {{-- Tombol Presensi Mobile --}}
            <a
                href="{{ route('mobile.dashboard') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
                <x-heroicon-m-device-phone-mobile class="w-4 h-4 shrink-0 text-amber-500" />
                <span>Presensi Mobile</span>
            </a>

            {{-- Tombol Keluar (Logout) --}}
            <form action="{{ $logoutUrl }}" method="post" class="w-full pt-1 border-t border-gray-100 dark:border-white/5">
                @csrf
                <button
                    type="submit"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer"
                >
                    <x-heroicon-m-arrow-left-on-rectangle class="w-4 h-4 shrink-0 text-red-500" />
                    <span>Keluar / Logout</span>
                </button>
            </form>
        </div>

        {{-- Kartu Profil Pengguna (Trigger Klik untuk Buka / Tutup Menu) --}}
        <button
            type="button"
            @click="open = !open"
            class="w-full flex items-center gap-2.5 p-2 rounded-xl text-left bg-white/70 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 border border-gray-200/80 dark:border-white/10 transition-all cursor-pointer shadow-xs focus:outline-hidden"
            :class="{ 'ring-2 ring-primary-500/40 border-primary-500/40': open }"
            title="Klik untuk membuka menu akun"
        >
            <x-filament-panels::avatar.user :user="$user" class="w-9 h-9 shrink-0 rounded-full ring-2 ring-primary-500/20" />
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                    {{ $user->name }}
                </p>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                    {{ $user->email }}
                </p>
            </div>
            <x-heroicon-m-chevron-up
                class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
                ::class="{ 'rotate-180': !open }"
            />
        </button>
    </div>

    {{-- Tampilan saat sidebar diperkecil (Collapsed Desktop) --}}
    <div x-show="!$store.sidebar.isOpen" class="flex flex-col items-center gap-2 py-1">
        <a href="{{ $editProfileUrl }}" wire:navigate title="{{ $user->name }} - Edit Profil">
            <x-filament-panels::avatar.user :user="$user" class="w-8 h-8 rounded-full ring-1 ring-primary-500/30 hover:opacity-80 transition-opacity" />
        </a>

        <a
            href="{{ $editProfileUrl }}"
            wire:navigate
            title="Edit Profil"
            class="p-1.5 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors {{ $isEditProfileActive ? 'text-primary-500 bg-primary-50 dark:bg-primary-500/10' : '' }}"
        >
            <x-heroicon-m-user-circle class="w-5 h-5" />
        </a>

        <form action="{{ $logoutUrl }}" method="post">
            @csrf
            <button
                type="submit"
                title="Keluar / Logout"
                class="p-1.5 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer"
            >
                <x-heroicon-m-arrow-left-on-rectangle class="w-5 h-5" />
            </button>
        </form>
    </div>
</div>
@endif
