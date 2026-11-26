@props([
    'label' => 'Upload File PDF (Maks. 2 MB)',
    'file' => null,
    'placeholder' => 'Ketuk untuk pilih file (PDF)',
])

@php
    $modelName = $attributes->wire('model')->value();
@endphp

<div>
    <label class="block text-xs font-bold text-slate-700 mb-1">{{ $label }}</label>
    <label class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/70 px-3 py-3 text-center cursor-pointer hover:border-sky-500 transition-colors">
        <i class="fa-solid fa-file-pdf text-lg text-rose-500"></i>
        <span class="text-[11px] font-semibold text-slate-700">
            {{ $file ? $file->getClientOriginalName() : $placeholder }}
        </span>
        <input type="file" {{ $attributes->wire('model') }} accept="application/pdf,.pdf" class="hidden">
    </label>
    @if ($modelName)
        <div wire:loading wire:target="{{ $modelName }}" class="text-[11px] text-sky-600 font-medium mt-1">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengunggah file...
        </div>
        @error($modelName)
            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    @endif
</div>
