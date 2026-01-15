<div class="space-y-6">
    <!-- Search Bar and Create Button -->
    <div class="flex justify-between items-center gap-4">
        <div class="flex-1 max-w-md relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                placeholder="Search configurations...">
        </div>

        <x-filament::button tag="a"
            href="{{ \Iperamuna\SupervisorManager\Filament\Pages\SupervisorConfigEdit::getUrl() }}"
            icon="heroicon-m-plus">
            New Config
        </x-filament::button>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($configs as $config)
            <x-supervisor-manager::config-supervisor-card 
                :config="$config" 
                :sync-action="$this->syncAction"
                :deploy-action="$this->deployAction" 
            />
        @empty
            <div class="col-span-full py-12 text-center text-gray-500">
                <p>No configurations found.</p>
            </div>
        @endforelse
    </div>
    <x-filament-actions::modals />
</div>