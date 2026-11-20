@props([
    'maxHeight' => 'max-h-[360px]',
    'label' => null,
    'count' => null,
])

<div>
    @if ($label || !is_null($count))
        <div class="flex items-center justify-between mb-2 px-1">
            <span class="text-xs font-extrabold text-slate-700">{{ $label }}</span>
            @if (!is_null($count))
                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold">
                    {{ $count }} Data
                </span>
            @endif
        </div>
    @endif
    <div {{ $attributes->merge(['class' => $maxHeight . ' overflow-y-auto pr-1 space-y-3']) }}>
        {{ $slot }}
    </div>
</div>
