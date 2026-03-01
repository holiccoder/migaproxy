<x-filament-panels::page>
    <form wire:submit="sendBulkNotification" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" style="margin-top: 1.5rem;">
            Send To All Users
        </x-filament::button>
    </form>
</x-filament-panels::page>
