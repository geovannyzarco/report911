<x-filament-panels::page>
    <form wire:submit="buscar">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit" wire:loading.remove wire:target="buscar">
                Buscar Eventos
            </x-filament::button>

            <span wire:loading wire:target="buscar" class="ml-3 text-sm text-gray-500">
                Consultando...
            </span>
        </div>
    </form>

    <div class="mt-6">
        <livewire:event-report-table :busqueda="$busqueda" />
    </div>
</x-filament-panels::page>
