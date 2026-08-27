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
            <div class="mb-4">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Se encontraron <strong class="text-gray-900 dark:text-white">{{ number_format(count($this->tableRecords)) }}</strong> eventos
                    del <strong class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</strong>
                    al <strong class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</strong>
                </span>
            </div>

            <div class="mb-4">
                <input
                    type="text"
                    wire:model.live="busqueda"
                    placeholder="Filtrar por numero de evento..."
                    class="w-full max-w-md rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm shadow-sm
                           focus:border-primary-500 focus:ring-primary-500 focus:outline-none
                           dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                />
            </div>

            {{ $this->table }}
        </div>
    @else
        <div class="text-center py-20 text-gray-500 dark:text-gray-400">
            <p class="text-lg font-medium mb-1">Selecciona un rango de fechas</p>
            <p class="text-sm">Usa los filtros de arriba para consultar los eventos del sistema CAD.</p>
        </div>
    @endif
</x-filament-panels::page>
