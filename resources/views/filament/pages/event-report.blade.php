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
                <div class="fi-ta-ctn w-full overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <table class="fi-ta-table w-full text-start divide-y divide-gray-200 dark:divide-gray-700" style="table-layout: fixed; min-width: 1200px;">
                        <thead class="fi-ta-header bg-gray-50 dark:bg-gray-800/50">
                            <tr class="fi-ta-header-row border-b border-gray-200 dark:border-gray-700">
                                <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 40px;">#</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 180px;">Evento</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 200px;">Tipo</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 150px;">Telefonista</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 150px;">Despachador</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 80px;">Llamada</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 80px;">Creacion</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 80px;">Despacho</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 80px;">En Sitio</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 80px;">Terminado</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 80px;">Cierre</th>
                                <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 80px;">Tiempo</th>
                            </tr>
                        </thead>
                        <tbody class="fi-ta-body divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getResultadosFiltrados() as $index => $row)
                                <tr class="fi-ta-row bg-white transition-colors hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm font-bold text-gray-950 dark:text-white whitespace-nowrap">{{ $row->{'Numero de Evento'} }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $row->{'Tipo de Evento'} }}">{{ $row->{'Tipo de Evento'} }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $row->Telefonista }}">{{ $row->Telefonista }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $row->Despachador }}">{{ $row->Despachador }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Llamada'} ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Creacion'} ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Despacho'} ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora En Sitio'} ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Terminado'} ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Cierre'} ?? '-' }}</td>
                                    <td class="fi-ta-cell px-3 py-3 text-center text-sm font-bold text-gray-950 dark:text-white whitespace-nowrap">{{ $row->{'Tiempo Total'} ?? '-' }}</td>
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
        <div class="text-center py-20 text-gray-500 dark:text-gray-400">
            <p class="text-lg font-medium mb-1">Selecciona un rango de fechas</p>
            <p class="text-sm">Usa los filtros de arriba para consultar los eventos del sistema CAD.</p>
        </div>
    @endif
</x-filament-panels::page>
