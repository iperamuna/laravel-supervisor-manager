<x-filament-panels::page>
    <form wire:submit="save">
        <div
            class="mb-4 p-4 text-sm text-blue-800 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-200 rounded-lg border border-blue-200 dark:border-blue-800">
            This configuration will be saved locally to
            <strong>{{ config('supervisor-manager.supervisors_dir') }}</strong>. You can then deploy it to the active
            supervisor configuration.
        </div>

        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="submit">
                Save Configuration
            </x-filament::button>

            <x-filament::button tag="a"
                href="{{ \Iperamuna\SupervisorManager\Filament\Pages\SupervisorConfigs::getUrl() }}" color="gray">
                Cancel
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>