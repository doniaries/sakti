@props([
    'placeholder' => 'HH:MM',
    'buttonClass' =>
        'w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none',
])

<div x-data="{
    value: '',
    open: false,
    selHour: '08',
    selMinute: '00',
    hours: Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0')),
    minutes: Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0')),
    init() {
        this.$watch('open', (isOpen) => {
            if (isOpen) {
                const m = (this.value || '').match(/^(\d{2}):(\d{2})/);
                this.selHour = m ? m[1] : '08';
                this.selMinute = m ? m[2] : '00';
            }
        });
    },
    apply() {
        this.value = `${this.selHour}:${this.selMinute}`;
        this.open = false;
    },
    selectNow() {
        const now = new Date();
        this.selHour = String(now.getHours()).padStart(2, '0');
        this.selMinute = String(now.getMinutes()).padStart(2, '0');
        this.apply();
    }
}" x-modelable="value" {{ $attributes->whereStartsWith('wire:model') }} class="relative w-full">
    <button type="button" @click="open = true"
        class="{{ $buttonClass }} flex items-center justify-between gap-2 text-left cursor-pointer">
        <span class="truncate font-mono" :class="value ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400'"
            x-text="value ? value : @js($placeholder)"></span>
        <i class="fa-regular fa-clock text-sky-600 shrink-0 text-xs"></i>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false"
            style="z-index: 1100;"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-end sm:items-center justify-center p-3 sm:p-4"
            @click.self="open = false">
            <div class="w-full max-w-xs rounded-3xl bg-white dark:bg-zinc-900 p-4 shadow-2xl border border-slate-100 dark:border-zinc-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-extrabold text-slate-800 dark:text-zinc-100">Pilih Waktu</span>
                    <span class="font-mono text-base font-black text-sky-600 dark:text-sky-400" x-text="`${selHour}:${selMinute}`"></span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-1">Jam</div>
                        <div class="max-h-48 overflow-y-auto rounded-xl border border-slate-100 dark:border-zinc-800 p-1 space-y-0.5">
                            <template x-for="h in hours" :key="h">
                                <button type="button" @click="selHour = h"
                                    class="w-full py-1.5 rounded-lg text-xs font-mono font-bold transition cursor-pointer"
                                    :class="selHour === h ? 'bg-sky-600 text-white' : 'text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800'"
                                    x-text="h"></button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-1">Menit</div>
                        <div class="max-h-48 overflow-y-auto rounded-xl border border-slate-100 dark:border-zinc-800 p-1 space-y-0.5">
                            <template x-for="m in minutes" :key="m">
                                <button type="button" @click="selMinute = m"
                                    class="w-full py-1.5 rounded-lg text-xs font-mono font-bold transition cursor-pointer"
                                    :class="selMinute === m ? 'bg-sky-600 text-white' : 'text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800'"
                                    x-text="m"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-zinc-800">
                    <button type="button" @click="selectNow()"
                        class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer">
                        Sekarang
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="open = false"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 cursor-pointer">
                            Tutup
                        </button>
                        <button type="button" @click="apply()"
                            class="px-4 py-1.5 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white cursor-pointer">
                            Pilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
