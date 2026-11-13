@props([
    'actions' => null,
    'description' => null,
    'heading' => null,
    'icon' => null,
    'contained' => true,
])

<div {{ $attributes->class([
    'fi-empty-state flex flex-1 flex-col items-center justify-center p-6 text-center',
    'rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10' => $contained,
]) }}>
    <div class="fi-empty-state-content max-w-md">
        @if ($icon)
            <div class="fi-empty-state-icon-bg mb-4 inline-flex items-center justify-center rounded-full bg-gray-100 p-3 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <x-filament::icon
                    :icon="$icon"
                    class="fi-empty-state-icon h-6 w-6"
                />
            </div>
        @endif

        @if ($heading)
            <h4 class="fi-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ $heading }}
            </h4>
        @endif

        @if ($description)
            <p class="fi-empty-state-description mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        @endif

        @if ($actions)
            <div class="fi-empty-state-actions mt-6 flex flex-wrap items-center justify-center gap-3">
                {{ $actions }}
            </div>
        @endif

        {{ $slot ?? '' }}
    </div>
</div>

