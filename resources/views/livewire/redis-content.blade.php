<div class="flex flex-col gap-6 h-[calc(100vh-12rem)]">
    <!-- Top Row: Search and Key List -->
    <div
        class="flex flex-col bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden h-1/3 min-h-[250px] resize-y">
        <!-- Search Header -->
        <div class="p-4 border-b border-gray-100 dark:border-white/10 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-950 dark:text-white">Redis Keys</h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">Connection:</span>
                    <select wire:model.live="connection"
                        class="text-xs border-gray-300 rounded-lg dark:bg-white/5 dark:border-white/10 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500 py-1">
                        @foreach($connections as $conn)
                            <option value="{{ $conn }}">{{ ucfirst($conn) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <div class="flex-1">
                    <label for="pattern" class="sr-only">Search Pattern</label>
                    <input wire:model.defer="pattern" wire:keydown.enter="loadKeys" type="text" id="pattern"
                        class="w-full px-3 py-2 text-sm border-gray-300 rounded-lg dark:bg-white/5 dark:border-white/10 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Search pattern (e.g. *, user:*, sess:*)">
                </div>
                <button wire:click="loadKeys"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400">
                    <span wire:loading.remove wire:target="loadKeys">Search</span>
                    <span wire:loading wire:target="loadKeys">...</span>
                </button>
            </div>
            @if($error)
                <div class="text-xs text-red-500 mt-1">{{ $error }}</div>
            @endif
        </div>

        <!-- Key List -->
        <div class="flex-1 overflow-y-auto p-2">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                @forelse($keys as $key)
                    <button wire:click="selectKey('{{ $key }}')"
                        class="text-left px-3 py-2 text-sm rounded-md transition-colors truncate border border-transparent {{ $selectedKey === $key ? 'bg-primary-50 text-primary-600 border-primary-200 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-900' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5 border-gray-100 dark:border-white/5' }}"
                        title="{{ $key }}">
                        {{ $key }}
                    </button>
                @empty
                    <div class="col-span-full text-sm text-gray-400 text-center py-8">
                        No keys found. Try searching with a pattern like '*' or 'laravel_database_*'.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="p-2 border-t border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-white/5">
            <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Showing top {{ count($keys) }} matches
            </div>
        </div>
    </div>

    <!-- Bottom Row: Key Content Viewer -->
    <div
        class="flex-1 flex flex-col bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden min-h-0">
        @if($selectedKey)
            <div
                class="p-4 border-b border-gray-100 dark:border-white/10 flex justify-between items-center bg-gray-50/50 dark:bg-white/5 flex-shrink-0">
                <div class="flex-1 min-w-0 mr-4">
                    <h3 class="font-mono text-sm font-semibold text-gray-950 dark:text-white truncate select-all"
                        title="{{ $selectedKey }}">
                        {{ $selectedKey }}
                    </h3>
                    <div class="flex flex-wrap gap-3 text-xs mt-1">
                        <span
                            class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400 uppercase">
                            {{ $keyType }}
                        </span>
                        <span class="inline-flex items-center text-gray-500 dark:text-gray-400" title="Time to Live">
                            TTL: <span
                                class="font-mono ml-1">{{ $keyTtl == -1 ? 'None' : ($keyTtl == -2 ? 'Expired' : $keyTtl . 's') }}</span>
                        </span>
                    </div>
                </div>
                <div>
                    <button wire:click="deleteKey" wire:confirm="Are you sure you want to delete this key?"
                        class="text-xs text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300 font-medium px-3 py-1.5 border border-red-200 dark:border-red-900/50 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        Delete Key
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-auto p-4 font-mono text-sm relative">
                @if(is_array($keyContent) || is_object($keyContent))
                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-black/20 p-4 rounded-lg border border-gray-200 dark:border-white/10">
                            <!-- Helper to display content nicely -->
                            @if($keyType === 'hash' && count($keyContent) > 0 && !isset($keyContent[0]))
                                <!-- Hash Table Display -->
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-xs sm:text-sm">
                                    <thead class="bg-gray-100 dark:bg-white/5">
                                        <tr>
                                            <th scope="col"
                                                class="px-3 py-2 text-left font-semibold text-gray-500 dark:text-gray-400">Field
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left font-semibold text-gray-500 dark:text-gray-400">Value
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                        @foreach($keyContent as $field => $value)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                                <td
                                                    class="px-3 py-2 align-top text-gray-600 dark:text-gray-300 font-medium border-r border-gray-100 dark:border-white/5 whitespace-nowrap">
                                                    {{ $field }}</td>
                                                <td class="px-3 py-2 text-gray-800 dark:text-gray-200 break-all whitespace-pre-wrap">
                                                    {{ is_string($value) ? $value : json_encode($value) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <!-- Array/Object/List Display -->
                                <pre
                                    class="text-xs sm:text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-all">{{ print_r($keyContent, true) }}</pre>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="h-full">
                        <textarea readonly
                            class="w-full h-full p-4 bg-gray-50 dark:bg-black/20 rounded-lg border border-gray-200 dark:border-white/10 text-gray-800 dark:text-gray-200 font-mono text-xs sm:text-sm resize-none focus:ring-0 focus:border-gray-200 dark:focus:border-white/10">{{ $keyContent }}</textarea>
                    </div>
                @endif
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <p>Select a key from the list above to view its content.</p>
                <p class="text-sm mt-2 opacity-75">Content will be automatically parsed (JSON or Serialized PHP)</p>
            </div>
        @endif
    </div>
</div>