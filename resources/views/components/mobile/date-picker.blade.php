@props([
    'placeholder' => 'dd/mm/yyyy',
    'min' => null,
    'max' => null,
    'clearable' => false,
    'buttonClass' =>
        'w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none',
])

<div x-data="{
    value: '',
    open: false,
    viewYear: new Date().getFullYear(),
    viewMonth: new Date().getMonth(),
    minDate: @js($min),
    maxDate: @js($max),
    months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    days: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    init() {
        this.$watch('open', (isOpen) => {
            if (isOpen) {
                const base = this.parseYmd(this.value) || this.parseYmd(this.minDate) || new Date();
                const targetYear = base.getFullYear();
                const targetMonth = base.getMonth();
                this.viewYear = targetYear;
                this.viewMonth = targetMonth;
                this.$nextTick(() => {
                    this.viewYear = targetYear;
                    this.viewMonth = targetMonth;
                });
            }
        });
    },
    parseYmd(str) {
        if (!str || typeof str !== 'string') return null;
        const m = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return null;
        return new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
    },
    formatDisplay(str) {
        const d = this.parseYmd(str);
        if (!d) return '';
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        return `${dd}/${mm}/${d.getFullYear()}`;
    },
    toYmd(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    },
    isDisabled(ymd) {
        if (this.minDate && ymd < this.minDate) return true;
        if (this.maxDate && ymd > this.maxDate) return true;
        return false;
    },
    get years() {
        const cur = new Date().getFullYear() + 1;
        const list = [];
        for (let y = cur; y >= 1950; y--) list.push(y);
        return list;
    },
    get calendarCells() {
        const first = new Date(this.viewYear, this.viewMonth, 1);
        const startOffset = (first.getDay() + 6) % 7;
        const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
        const cells = [];
        for (let i = 0; i < startOffset; i++) cells.push(null);
        for (let d = 1; d <= daysInMonth; d++) {
            const ymd = this.toYmd(this.viewYear, this.viewMonth, d);
            cells.push({ day: d, ymd, disabled: this.isDisabled(ymd) });
        }
        return cells;
    },
    prevMonth() {
        if (this.viewMonth === 0) { this.viewMonth = 11;
            this.viewYear--; } else { this.viewMonth--; }
    },
    nextMonth() {
        if (this.viewMonth === 11) { this.viewMonth = 0;
            this.viewYear++; } else { this.viewMonth++; }
    },
    selectDate(ymd) {
        if (this.isDisabled(ymd)) return;
        this.value = ymd;
        this.open = false;
    },
    selectToday() {
        const t = new Date();
        const ymd = this.toYmd(t.getFullYear(), t.getMonth(), t.getDate());
        if (!this.isDisabled(ymd)) {
            this.value = ymd;
            this.open = false;
        }
    }
}" x-modelable="value" {{ $attributes->whereStartsWith('wire:model') }} class="relative w-full">
    <button type="button" @click="open = true"
        class="{{ $buttonClass }} flex items-center justify-between gap-2 text-left cursor-pointer">
        <span class="truncate" :class="value ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400'"
            x-text="value ? formatDisplay(value) : @js($placeholder)"></span>
        <i class="fa-regular fa-calendar text-sky-600 shrink-0 text-xs"></i>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false"
            style="z-index: 1100;"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-end sm:items-center justify-center p-3 sm:p-4"
            @click.self="open = false">
            <div class="w-full max-w-sm rounded-3xl bg-white p-4 shadow-2xl border border-slate-100 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <button type="button" @click="prevMonth()"
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center cursor-pointer shrink-0">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>

                    <div class="flex items-center gap-1.5 flex-1 justify-center">
                        <select x-model.number="viewMonth"
                            class="text-xs font-extrabold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <template x-for="(mName, idx) in months" :key="idx">
                                <option :value="idx" :selected="idx === viewMonth" x-text="mName"></option>
                            </template>
                        </select>
                        <select x-model.number="viewYear"
                            class="text-xs font-extrabold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <template x-for="yr in years" :key="yr">
                                <option :value="yr" :selected="yr === viewYear" x-text="yr"></option>
                            </template>
                        </select>
                    </div>

                    <button type="button" @click="nextMonth()"
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center cursor-pointer shrink-0">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center">
                    <template x-for="d in days" :key="d">
                        <div class="text-xs font-extrabold text-slate-400 uppercase py-1" x-text="d"></div>
                    </template>

                    <template x-for="(cell, idx) in calendarCells" :key="idx">
                        <div>
                            <template x-if="cell">
                                <button type="button" :disabled="cell.disabled" @click="selectDate(cell.ymd)"
                                    class="w-full h-9 rounded-xl text-xs font-bold transition cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                    :class="value === cell.ymd ?
                                        'bg-sky-600 text-white shadow-sm shadow-sky-600/30' :
                                        'text-slate-700 hover:bg-sky-50'"
                                    x-text="cell.day"></button>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100 gap-2">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectToday()"
                            class="px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold cursor-pointer">
                            Hari Ini
                        </button>
                        @if ($clearable)
                            <button type="button" @click="value = ''; open = false"
                                class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold cursor-pointer">
                                Hapus
                            </button>
                        @endif
                    </div>
                    <button type="button" @click="open = false"
                        class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
