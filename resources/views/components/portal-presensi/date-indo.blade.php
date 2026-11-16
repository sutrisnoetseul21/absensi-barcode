@props([
    'model',
    'value' => null,
    'nullable' => false,
    'live' => false,
    'placeholder' => 'Pilih tanggal',
    'bgClass' => 'bg-slate-50',
    'buttonClass' => null,
    'popoverAlign' => 'left',
])

@php
    $todayJakarta = now('Asia/Jakarta')->toDateString();
    $baseYear = (int) now('Asia/Jakarta')->year;
    $minYear = $baseYear - 5;
    $maxYear = $baseYear + 5;
    $initialFormatted = '';
    if (!empty($value)) {
        try {
            $parsed = \Carbon\Carbon::parse($value);
            $initialFormatted = $parsed->locale('id')->translatedFormat('l, d F Y');
            $minYear = min($minYear, (int) $parsed->year);
            $maxYear = max($maxYear, (int) $parsed->year);
        } catch (\Throwable $e) {
            $initialFormatted = (string) $value;
        }
    }
    $monthsIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $entangleExpr = $live
        ? "\$wire.entangle('{$model}').live"
        : "\$wire.entangle('{$model}')";
    $defaultButtonClass = "w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-sm font-bold rounded-xl border border-slate-200 {$bgClass} hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-left cursor-pointer";
    $resolvedButtonClass = $buttonClass ?: $defaultButtonClass;
    $popoverPosClass = $popoverAlign === 'right' ? 'right-0' : 'left-0';
@endphp

<div
    x-data="{
        val: {!! $entangleExpr !!},
        open: false,
        viewYear: {{ (int) now('Asia/Jakarta')->year }},
        viewMonth: {{ (int) now('Asia/Jakarta')->month - 1 }},
        todayStr: '{{ $todayJakarta }}',
        daysLong: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
        monthsLong: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        init() {
            this.syncCalendarView(this.val);
            this.$watch('val', (next) => this.syncCalendarView(next));
        },
        parseIso(iso) {
            if (!iso || typeof iso !== 'string') return null;
            const m = iso.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (!m) return null;
            const y = parseInt(m[1], 10), mo = parseInt(m[2], 10) - 1, d = parseInt(m[3], 10);
            const dt = new Date(y, mo, d);
            if (isNaN(dt.getTime())) return null;
            return { y, mo, d, dt };
        },
        syncCalendarView(iso) {
            const p = this.parseIso(iso);
            if (p) {
                this.viewYear = p.y;
                this.viewMonth = p.mo;
            }
        },
        get formattedLabel() {
            const p = this.parseIso(this.val);
            if (!p) return '{{ $placeholder }}';
            const dayName = this.daysLong[p.dt.getDay()];
            const dd = String(p.d).padStart(2, '0');
            const monthName = this.monthsLong[p.mo];
            return `${dayName}, ${dd} ${monthName} ${p.y}`;
        },
        get hasValue() {
            return !!this.parseIso(this.val);
        },
        get calendarCells() {
            const first = new Date(this.viewYear, this.viewMonth, 1);
            const totalDays = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            // Monday = 0 ... Sunday = 6
            const startOffset = (first.getDay() + 6) % 7;
            const cells = [];
            for (let i = 0; i < startOffset; i++) cells.push(null);
            for (let day = 1; day <= totalDays; day++) {
                const iso = `${this.viewYear}-${String(this.viewMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                cells.push({ day, iso });
            }
            return cells;
        },
        prevMonth() {
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear--;
            } else {
                this.viewMonth--;
            }
        },
        nextMonth() {
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear++;
            } else {
                this.viewMonth++;
            }
        },
        selectDate(iso) {
            this.val = iso;
            this.open = false;
        },
        selectToday() {
            this.val = this.todayStr;
            this.syncCalendarView(this.todayStr);
            this.open = false;
        },
        clearDate() {
            this.val = null;
            this.open = false;
        }
    }"
    x-on:keydown.escape.window="open = false"
    x-on:click.outside="open = false"
    class="relative"
    :class="{ 'z-50': open }"
>
    <button
        type="button"
        x-on:click="open = !open; if (open) syncCalendarView(val);"
        class="{{ $resolvedButtonClass }}"
    >
        <span
            x-text="formattedLabel"
            :class="hasValue ? 'text-slate-800' : 'text-slate-400 font-medium'"
            class="truncate"
        >{{ $initialFormatted ?: $placeholder }}</span>

        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-transition.opacity.duration.150ms
        x-cloak
        class="absolute {{ $popoverPosClass }} z-50 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-3.5"
    >
        <div class="flex items-center justify-between gap-1.5 mb-3">
            <button type="button" x-on:click="prevMonth()"
                class="w-8 h-8 rounded-lg border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div class="flex items-center gap-1.5">
                <select
                    x-model.number="viewMonth"
                    x-effect="$el.value = String(viewMonth)"
                    aria-label="Bulan"
                    class="text-xs font-extrabold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                    @foreach($monthsIndo as $mIdx => $mName)
                        <option value="{{ $mIdx }}">{{ $mName }}</option>
                    @endforeach
                </select>
                <select
                    x-model.number="viewYear"
                    x-effect="$el.value = String(viewYear)"
                    aria-label="Tahun"
                    class="text-xs font-extrabold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                    @for($yr = $minYear; $yr <= $maxYear; $yr++)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endfor
                </select>
            </div>

            <button type="button" x-on:click="nextMonth()"
                class="w-8 h-8 rounded-lg border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-black text-slate-400 uppercase mb-1.5">
            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span class="text-rose-400">Min</span>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center">
            <template x-for="(cell, idx) in calendarCells" :key="idx">
                <div>
                    <template x-if="cell">
                        <button
                            type="button"
                            x-on:click="selectDate(cell.iso)"
                            x-text="cell.day"
                            :class="{
                                'bg-brand-primary text-white font-black shadow-sm': val === cell.iso,
                                'bg-indigo-50 text-brand-primary font-extrabold ring-1 ring-brand-primary/30': val !== cell.iso && todayStr === cell.iso,
                                'text-slate-700 hover:bg-slate-100 font-semibold': val !== cell.iso && todayStr !== cell.iso
                            }"
                            class="w-8 h-8 rounded-lg text-xs flex items-center justify-center mx-auto transition-colors cursor-pointer"
                        ></button>
                    </template>
                </div>
            </template>
        </div>

        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
            <button type="button" x-on:click="selectToday()"
                class="text-xs font-extrabold text-brand-primary hover:underline cursor-pointer">
                Hari Ini
            </button>
            @if($nullable)
                <button type="button" x-on:click="clearDate()"
                    class="text-xs font-bold text-slate-500 hover:text-rose-600 cursor-pointer">
                    Kosongkan
                </button>
            @endif
        </div>
    </div>
</div>

