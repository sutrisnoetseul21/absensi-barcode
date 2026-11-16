@props([
    'model',
    'value' => '07:00',
    'bgClass' => 'bg-slate-50',
    'compact' => false,
    'live' => false,
])

@php
    $parts = explode(':', (string) ($value ?: '00:00'));
    $initHour = str_pad($parts[0] ?? '00', 2, '0', STR_PAD_LEFT);
    $initMinute = str_pad($parts[1] ?? '00', 2, '0', STR_PAD_LEFT);
@endphp

<div
    x-data="{
        val: $wire.entangle('{{ $model }}'){{ $live ? '.live' : '' }},
        get parts() {
            const raw = (typeof this.val === 'string' && this.val.includes(':'))
                ? this.val
                : '{{ $initHour }}:{{ $initMinute }}';
            const [h = '00', m = '00'] = raw.split(':');
            return [h.padStart(2, '0').slice(0, 2), m.padStart(2, '0').slice(0, 2)];
        },
        get hour() { return this.parts[0]; },
        get minute() { return this.parts[1]; },
        updateHour(h) { this.val = h + ':' + this.minute; },
        updateMinute(m) { this.val = this.hour + ':' + m; },
    }"
    class="flex items-center justify-between {{ $compact ? 'gap-1 px-2 py-1 rounded-lg' : 'gap-2 px-3 py-2 rounded-xl' }} border border-slate-200 {{ $bgClass }} focus-within:bg-white focus-within:ring-2 focus-within:ring-brand-primary/30 focus-within:border-brand-primary transition-all"
>
    <div class="flex items-center {{ $compact ? 'gap-0.5' : 'gap-1.5' }}">
        <svg class="{{ $compact ? 'w-3.5 h-3.5 mr-0.5' : 'w-4 h-4 mr-1' }} text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>

        <select
            aria-label="Jam (24 jam)"
            :value="hour"
            x-on:change="updateHour($event.target.value)"
            class="bg-transparent {{ $compact ? 'text-xs' : 'text-sm' }} font-black text-slate-800 focus:outline-none cursor-pointer py-0.5 pr-0.5 tabular-nums"
        >
            @for($h = 0; $h <= 23; $h++)
                @php $hh = str_pad((string) $h, 2, '0', STR_PAD_LEFT); @endphp
                <option value="{{ $hh }}">{{ $hh }}</option>
            @endfor
        </select>

        <span class="{{ $compact ? 'text-xs' : 'text-sm' }} font-black text-slate-400">:</span>

        <select
            aria-label="Menit"
            :value="minute"
            x-on:change="updateMinute($event.target.value)"
            class="bg-transparent {{ $compact ? 'text-xs' : 'text-sm' }} font-black text-slate-800 focus:outline-none cursor-pointer py-0.5 pr-0.5 tabular-nums"
        >
            @for($m = 0; $m <= 59; $m++)
                @php $mm = str_pad((string) $m, 2, '0', STR_PAD_LEFT); @endphp
                <option value="{{ $mm }}">{{ $mm }}</option>
            @endfor
        </select>
    </div>

    <span class="inline-flex items-center {{ $compact ? 'px-1.5 py-0.5 text-[10px]' : 'px-2 py-0.5 text-[11px]' }} rounded-md font-extrabold tracking-wide bg-indigo-50 text-brand-primary border border-indigo-100 shrink-0">
        {{ $compact ? 'WIB' : 'WIB (24 Jam)' }}
    </span>
</div>

