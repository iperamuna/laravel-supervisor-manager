<div class="grid gap-6">
    @if($error)
        <div class="p-4 bg-red-50 text-red-500 rounded-lg dark:bg-red-900/10 dark:text-red-400">
            Error connecting to Redis: {{ $error }}
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Status Card -->
            <div class="p-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg dark:bg-green-900/10 dark:text-green-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">Active</div>
                        <div class="text-xs text-gray-400">Version: {{ $info['redis_version'] ?? 'Unknown' }}</div>
                    </div>
                </div>
            </div>

            <!-- Memory Card -->
            <div class="p-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/10 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory Used</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $projectMemory }}</div>
                         <div class="text-xs text-gray-400">Peak: {{ $info['used_memory_peak_human'] ?? '?' }}</div>
                    </div>
                </div>
            </div>

             <!-- Uptime Card -->
             <div class="p-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg dark:bg-yellow-900/10 dark:text-yellow-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Uptime</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ floor(($info['uptime_in_seconds'] ?? 0) / 86400) }} Days</div>
                         <div class="text-xs text-gray-400">{{ gmdate("H:i:s", ($info['uptime_in_seconds'] ?? 0) % 86400) }}</div>
                    </div>
                </div>
            </div>

             <!-- Connections Card -->
             <div class="p-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-lg dark:bg-purple-900/10 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Connections</div>
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $info['connected_clients'] ?? 0 }}</div>
                         <div class="text-xs text-gray-400">Total processed: {{ $info['total_connections_received'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Configuration -->
            <div class="p-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white mb-4">Configuration</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Host</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $config['host'] ?? '127.0.0.1' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Port</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $config['port'] ?? '6379' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Database</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $config['database'] ?? '0' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Prefix</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $config['prefix'] ?? 'None' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Performance Stats -->
            <div class="p-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white mb-4">Performance</h3>
                 <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Instantaneous Ops</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $info['instantaneous_ops_per_sec'] ?? 0 }} / sec</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hit Rate</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            @php
                                $hits = $info['keyspace_hits'] ?? 0;
                                $misses = $info['keyspace_misses'] ?? 0;
                                $total = $hits + $misses;
                                $rate = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
                            @endphp
                            {{ $rate }}%
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Input</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format(($info['total_net_input_bytes'] ?? 0) / 1024 / 1024, 2) }} MB</dd>
                    </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Output</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format(($info['total_net_output_bytes'] ?? 0) / 1024 / 1024, 2) }} MB</dd>
                    </div>
                </dl>
            </div>
        </div>
    @endif
</div>
