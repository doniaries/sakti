<div class="flex flex-col min-h-screen bg-[#1B6CA8]">
    <!-- Header -->
    <div class="h-[35vh] flex flex-col justify-center items-center">
        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mb-4 transition transform hover:scale-105">
            <i class="fa-solid fa-fingerprint text-white text-5xl"></i>
        </div>
        <h1 class="text-white text-2xl font-bold">Absensi LHK</h1>
        <p class="text-white/80 text-sm mt-1">Sistem Absensi & Laporan Kerja</p>
    </div>

    <!-- Form Card -->
    <div class="flex-1 bg-white rounded-t-[32px] p-8 pb-12 shadow-xl flex flex-col justify-start">
        <h2 class="text-2xl font-bold text-gray-800">Masuk</h2>
        <p class="text-gray-500 text-sm mt-2 mb-8">Silakan login dengan akun Anda</p>

        <form wire:submit="login" class="space-y-6">
            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-regular fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" wire:model="email" 
                        class="w-full pl-10 pr-4 py-3 bg-[#F5F7FA] border-none rounded-xl focus:ring-2 focus:ring-[#1B6CA8] transition @error('email') ring-2 ring-red-500 @enderror"
                        placeholder="Masukkan email Anda">
                </div>
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative" x-data="{ show: false }">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-gray-400"></i>
                    </div>
                    <input :type="show ? 'text' : 'password'" wire:model="password" 
                        class="w-full pl-10 pr-10 py-3 bg-[#F5F7FA] border-none rounded-xl focus:ring-2 focus:ring-[#1B6CA8] transition @error('password') ring-2 ring-red-500 @enderror"
                        placeholder="Masukkan password">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="show = !show">
                        <i class="fa-solid text-gray-400" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </div>
                </div>
                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            
            <!-- Remember Me -->
            <div class="flex items-center">
                <input type="checkbox" wire:model="remember" id="remember" class="w-4 h-4 text-[#1B6CA8] bg-gray-100 border-gray-300 rounded focus:ring-[#1B6CA8]">
                <label for="remember" class="ml-2 text-sm text-gray-600">Ingat Saya</label>
            </div>

            <button type="submit" 
                class="w-full bg-[#1B6CA8] text-white font-semibold py-3.5 rounded-xl shadow-lg hover:bg-[#155685] transition flex items-center justify-center mt-2 relative"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Masuk</span>
                <span wire:loading>
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </span>
            </button>
        </form>
    </div>
</div>
