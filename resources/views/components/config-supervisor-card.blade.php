@props(['config', 'syncAction', 'deployAction', 'stopAction', 'startAction'])

<div
    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200 relative group">

    <div class="flex justify-between items-start mb-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="p-1 rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd"
                                d="M2 3.5A1.5 1.5 0 0 1 3.5 2h9A1.5 1.5 0 0 1 14 3.5v11.75A2.75 2.75 0 0 0 16.75 18h-12A2.75 2.75 0 0 0 2 15.25V3.5Zm3.75 7a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    {{ $config['name'] }}
                </h3>

                @if (isset($config['status']))
                    @if ($config['status'] === 'new')
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            NEW
                        </span>
                    @elseif($config['status'] === 'modified')
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                            UPDATED
                        </span>
                    @elseif($config['status'] === 'system-only')
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200"
                            title="Exists in system directory but missing locally">
                            ORPHAN
                        </span>
                    @endif
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-1 font-mono">{{ $config['filename'] }}</p>
        </div>

        <div class="flex items-center gap-1">
            <!-- Sync / Copy Button -->
            @if (isset($config['status']) && ($config['status'] === 'new' || $config['status'] === 'modified' || $config['status'] === 'system-only'))
                {{ ($syncAction)(['filename' => $config['filename']]) }}
            @endif

            <!-- Deploy & Reload Button -->
            {{ ($deployAction)(['filename' => $config['filename']]) }}

            <!-- Stop/Start Button -->
            @if (isset($config['is_running']))
                @if ($config['is_running'])
                    {{ ($stopAction)(['program' => $config['program'] ?? '']) }}
                @else
                    {{ ($startAction)(['program' => $config['program'] ?? '']) }}
                @endif
            @endif

            <a href="{{ \Iperamuna\SupervisorManager\Filament\Pages\SupervisorConfigEdit::getUrl(['file' => $config['filename']]) }}"
                class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                title="Edit Configuration">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd"
                        d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54A6.993 6.993 0 0112.5 16.544l-.331 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.047 7.047 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.5 3.456l.331-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>

    <div class="space-y-3 text-sm">
        @if (isset($config['user']))
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase text-gray-500 w-16">User</span>
                <span class="text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">
                    {{ $config['user'] }}
                </span>
            </div>
        @endif

        @if (isset($config['numprocs']))
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase text-gray-500 w-16">Procs</span>
                <span class="text-gray-700 dark:text-gray-300 font-mono">
                    {{ $config['numprocs'] }}
                </span>
            </div>
        @endif

        <div class="pt-2">
            <span class="block text-xs font-semibold uppercase text-gray-500 mb-1">Command</span>
            <div class="bg-gray-50 dark:bg-gray-900 rounded p-2 border border-gray-100 dark:border-gray-800 font-mono text-xs text-gray-600 dark:text-gray-400 break-all line-clamp-2"
                title="{{ $config['command'] ?? '' }}">
                {{ $config['command'] ?? 'No command specified' }}
            </div>
        </div>

        <div class="flex gap-2 mt-2">
            @if (isset($config['autostart']) && ($config['autostart'] == 'true' || $config['autostart'] == true))
                <span
                    class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Autostart</span>
            @endif
            @if (isset($config['autorestart']) && ($config['autorestart'] == 'true' || $config['autorestart'] == true))
                <span class="text-[10px] font-bold uppercase text-blue-600 bg-blue-50 px-2 py-1 rounded">Autorestart</span>
            @endif
        </div>
    </div>

</div>