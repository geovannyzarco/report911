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

    @if($busquedaEjecutada)
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @else
        <div class="text-center py-20 text-gray-500 dark:text-gray-400">
            <p class="text-lg font-medium mb-1">Selecciona un rango de fechas</p>
            <p class="text-sm">Usa los filtros de arriba para consultar los eventos del sistema CAD.</p>
        </div>
    @endif
</x-filament-panels::page>
