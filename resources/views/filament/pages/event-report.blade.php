<x-filament-panels::page>
    {{-- Formulario de filtros --}}
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

    {{-- Resultados --}}
    @if($busquedaEjecutada)
        <div class="mt-6">
            {{-- Resumen --}}
            <div class="mb-4">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Se encontraron <strong class="text-gray-900 dark:text-white">{{ number_format($totalRegistros) }}</strong> eventos
                    del <strong class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</strong>
                    al <strong class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</strong>
                </span>
            </div>

            {{-- Campo de busqueda por numero de evento --}}
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

            {{-- Tabla de resultados --}}
            @if(count($this->getResultadosFiltrados()) > 0)
                <div class="fi-ta-ctn inline-block w-full overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <table class="fi-ta-table w-full text-start divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="fi-ta-header">
                            <tr class="fi-ta-header-row">
                                <x-filament-tables::header-cell index="index">#</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="evento">Evento</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="tipo">Tipo</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="telefonista">Telefonista</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="despachador">Despachador</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="llamada" class="text-center">Llamada</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="creacion" class="text-center">Creacion</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="despacho" class="text-center">Despacho</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="ensitio" class="text-center">En Sitio</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="terminado" class="text-center">Terminado</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="cierre" class="text-center">Cierre</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell index="tiempo" class="text-center">Tiempo</x-filament-tables::header-cell>
                            </tr>
                        </thead>
                        <tbody class="fi-ta-body divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getResultadosFiltrados() as $index => $row)
                                <tr class="fi-ta-row transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm font-bold text-gray-950 dark:text-white">
                                        {{ $row->{'Numero de Evento'} }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->{'Tipo de Evento'} }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->Telefonista }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->Despachador }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->{'Hora Llamada'} ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->{'Hora Creacion'} ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->{'Hora Despacho'} ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->{'Hora En Sitio'} ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->{'Hora Terminado'} ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $row->{'Hora Cierre'} ?? '-' }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm font-bold text-gray-950 dark:text-white">
                                        {{ $row->{'Tiempo Total'} ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <p>No se encontraron eventos con ese numero.</p>
                </div>
            @endif
        </div>
    @else
        {{-- Estado inicial: sin busqueda --}}
        <div class="text-center py-20 text-gray-500 dark:text-gray-400">
            <p class="text-lg font-medium mb-1">Selecciona un rango de fechas</p>
            <p class="text-sm">Usa los filtros de arriba para consultar los eventos del sistema CAD.</p>
        </div>
    @endif
</x-filament-panels::page>
