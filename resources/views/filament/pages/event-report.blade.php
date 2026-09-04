<x-filament-panels::page>
    {{-- El formulario gestiona los campos de fecha y busqueda via Filament --}}
    <form>
        {{ $this->form }}

        {{-- Seccion de acciones: dos botones con responsabilidades distintas --}}
        <div class="mt-4 flex flex-wrap items-center gap-3">

            {{-- Boton 1: Buscar por rango de fechas (requiere Fecha Desde y Fecha Hasta) --}}
            <x-filament::button
                wire:click="buscarPorFecha"
                wire:loading.attr="disabled"
                wire:loading.remove
                wire:target="buscarPorFecha"
                icon="heroicon-m-calendar-days"
            >
                Buscar por Fecha
            </x-filament::button>

            {{-- Boton 2: Buscar por numero de evento (usa solo el campo busqueda) --}}
            <x-filament::button
                wire:click="buscarPorEvento"
                wire:loading.attr="disabled"
                wire:loading.remove
                wire:target="buscarPorEvento"
                icon="heroicon-m-magnifying-glass"
                color="gray"
            >
                Buscar Evento
            </x-filament::button>

            {{-- Indicador de carga para busqueda por fecha --}}
            <span wire:loading wire:target="buscarPorFecha" class="text-sm text-gray-500">
                Consultando...
            </span>

            {{-- Indicador de carga para busqueda por evento --}}
            <span wire:loading wire:target="buscarPorEvento" class="text-sm text-gray-500">
                Buscando evento...
            </span>
        </div>
    </form>

    <div class="mt-6">
        <livewire:event-report-table :busqueda="$busqueda" />
    </div>
</x-filament-panels::page>

