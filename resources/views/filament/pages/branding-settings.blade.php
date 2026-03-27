<x-filament-panels::page>
    <div class="w-full md:w-1/3">
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>
