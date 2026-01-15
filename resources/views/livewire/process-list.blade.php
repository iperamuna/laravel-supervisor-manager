<div wire:poll.5s class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($processes as $process)
        @php
            $isRunning = $process['state'] == 20; // RUNNING
            $isFatal = $process['state'] == 200; // FATAL
            $isBackoff = $process['state'] == 100; // BACKOFF

            $statusColor = match ((int) $process['state']) {
                20 => 'text-emerald-600 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800', // RUNNING
                200, 100 => 'text-red-600 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800', // FATAL, BACKOFF
                0 => 'text-gray-600 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-900/30 dark:border-gray-700', // STOPPED
                default => 'text-amber-600 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800', // STARTING, STOPPING, ETC
            };

            $borderColor = match ((int) $process['state']) {
                20 => 'border-l-emerald-500',
                200, 100 => 'border-l-red-500',
                0 => 'border-l-gray-400',
                default => 'border-l-amber-500',
            };
        @endphp

        <div
            class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200 border-l-4 {{ $borderColor }}">
            <div class="p-4 space-y-3">

                <!-- Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg">
                            {{ $process['group'] }}:{{ $process['name'] }}
                        </h3>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">
                            PID: {{ $process['pid'] ?: '-' }}
                        </div>
                    </div>

                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusColor }}">
                        {{ $process['statename'] }}
                        {{ ($this->restartProcessAction)(['group' => $process['group'], 'name' => $process['name']]) }}
                    </span>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-2 text-sm pt-2">
                    <div class="col-span-2">
                        <span class="block text-xs font-medium text-gray-500 uppercase tracking-widest">Uptime</span>
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ $this->formatUptime($process['start']) }}
                        </span>
                    </div>

                    @if(isset($process['user']))
                        <div class="col-span-2">
                            <span class="block text-xs font-medium text-gray-500 uppercase tracking-widest">User</span>
                            <span class="text-gray-700 dark:text-gray-300 font-mono text-xs">
                                {{ $process['user'] }}
                            </span>
                        </div>
                    @endif

                    @if(isset($process['command']))
                        <div class="col-span-2">
                            <span class="block text-xs font-medium text-gray-500 uppercase tracking-widest">Command</span>
                            <div class="relative group cursor-help">
                                <div
                                    class="truncate text-xs font-mono text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-1 rounded border border-gray-100 dark:border-gray-700">
                                    {{ $process['command'] }}
                                </div>
                                <!-- Tooltip -->
                                <div
                                    class="absolute z-10 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-opacity bg-black text-white text-xs rounded p-2 -mt-1 w-64 break-words">
                                    {{ $process['command'] }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer / Logs -->
                <!-- Footer / Logs -->
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-1">
                    <div class="group relative flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded p-1 -mx-1 transition-colors"
                        wire:click="mountAction('viewStdout', { group: '{{ $process['group'] }}', name: '{{ $process['name'] }}' })">
                        <span class="text-xs font-bold text-gray-400 w-10 group-hover:text-primary-500">LOGS</span>
                        <div
                            class="flex-1 truncate text-xs text-gray-500 font-mono bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded group-hover:bg-white dark:group-hover:bg-gray-700 border border-transparent group-hover:border-gray-200 dark:group-hover:border-gray-600">
                            {{ basename($process['stdout_logfile']) }}
                        </div>
                    </div>
                    <div class="group relative flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded p-1 -mx-1 transition-colors"
                        wire:click="mountAction('viewStderr', { group: '{{ $process['group'] }}', name: '{{ $process['name'] }}' })">
                        <span class="text-xs font-bold text-gray-400 w-10 group-hover:text-red-500">ERR</span>
                        <div
                            class="flex-1 truncate text-xs text-gray-500 font-mono bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded group-hover:bg-white dark:group-hover:bg-gray-700 border border-transparent group-hover:border-gray-200 dark:group-hover:border-gray-600">
                            {{ basename($process['stderr_logfile']) }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @empty
        <div
            class="col-span-full flex flex-col items-center justify-center p-12 bg-white dark:bg-gray-900 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
            <div class="h-12 w-12 text-gray-400 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No Processes Found</h3>
            <p class="text-gray-500 text-center mt-2 max-w-sm">
                Supervisor is reachable but isn't managing any processes, or check your connection settings.
            </p>
        </div>
    @endforelse
    <x-filament-actions::modals />
</div>