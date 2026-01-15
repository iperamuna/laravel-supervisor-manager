<div wire:poll.30s="checkStatus" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-bold bordershadow-sm uppercase tracking-wider
    @if($isRunning) bg-green-100 text-green-800 border-green-300 dark:bg-green-900/50 dark:text-green-300 dark:border-green-700
    @elseif($isError) bg-red-100 text-red-800 border-red-300 dark:bg-red-900/50 dark:text-red-300 dark:border-red-700
    @else bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600
    @endif">
    <!-- Indicator Dot -->
    <div class="relative flex h-4 w-4 shrink-0">
        @if($isRunning)
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span
                class="relative inline-flex rounded-full h-4 w-4 bg-green-500 shadow-md ring-2 ring-green-200 dark:ring-green-900"></span>
        @elseif($isError)
            <span class="animate-pulse absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span
                class="relative inline-flex rounded-full h-4 w-4 bg-red-600 shadow-md ring-2 ring-red-200 dark:ring-red-900"></span>
        @else
            <span class="relative inline-flex rounded-full h-4 w-4 bg-gray-400"></span>
        @endif
    </div>

    <!-- Status Text -->
    <span class="whitespace-nowrap capitalize mr-2">
        {{ $status }}
    </span>

    <!-- Restart Action -->
    {{ ($this->restartAction) }}

    <x-filament-actions::modals />
</div>