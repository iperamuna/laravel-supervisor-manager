<div class="space-y-4">
    <div class="flex justify-between items-center">
        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
            Viewing {{ strtoupper($type) }} for {{ $process }}
        </div>
        <div class="flex gap-2">
            <x-filament::button wire:click="refreshLogs" size="xs" color="gray" icon="heroicon-m-arrow-path">
                Refresh
            </x-filament::button>

            <x-filament::button wire:click="clearLogs" size="xs" color="danger" icon="heroicon-m-trash">
                Clear
            </x-filament::button>
        </div>
    </div>

    <div
        class="relative bg-gray-900 text-gray-200 p-4 rounded-lg font-mono text-xs overflow-auto h-[60vh] shadow-inner border border-gray-700">
        <pre class="whitespace-pre-wrap break-words">{{ $content ?: 'No logs found or empty.' }}</pre>
    </div>

    <div class="text-xs text-gray-400 text-right">
        Showing last {{ $length }} bytes
    </div>
</div>