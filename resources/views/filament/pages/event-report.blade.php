<x-filament-panels::page>
    {{-- Formulario de filtros --}}
    <form wire:submit="buscar">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit" wire:loading.remove>
                <x-heroicon-m-magnifying-glass class="w-5 h-5 mr-2" />
                Buscar Eventos
            </x-filament::button>

            <span wire:loading class="ml-3">
                <x-heroicon-m-arrow-path class="w-5 h-5 animate-spin text-primary-500" />
                Consultando...
            </span>
        </div>
    </form>

    {{-- Resultados --}}
    @if($busquedaEjecutada)
        <div class="mt-6">
            {{-- Resumen --}}
            <div class="mb-4 flex items-center gap-2">
                <x-heroicon-m-document-text class="w-5 h-5 text-gray-500" />
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Se encontraron <strong class="text-gray-900 dark:text-white">{{ number_format($totalRegistros) }}</strong> eventos
                    del <strong class="text-gray-900 dark:text-white">{{ $fechaDesde }}</strong>
                    al <strong class="text-gray-900 dark:text-white">{{ $fechaHasta }}</strong>
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
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Evento</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Telefonista</th>
                                <th class="px-4 py-3">Despachador</th>
                                <th class="px-4 py-3 text-center">Llamada</th>
                                <th class="px-4 py-3 text-center">Creacion</th>
                                <th class="px-4 py-3 text-center">Despacho</th>
                                <th class="px-4 py-3 text-center">En Sitio</th>
                                <th class="px-4 py-3 text-center">Terminado</th>
                                <th class="px-4 py-3 text-center">Cierre</th>
                                <th class="px-4 py-3 text-center">Tiempo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($this->getResultadosFiltrados() as $index => $row)
                                <tr class="bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        {{ $row->{'Numero de Evento'} }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-[200px] truncate">
                                        {{ $row->{'Tipo de Evento'} }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $row->Telefonista }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $row->Despachador }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs">
                                        {{ $row->{'Hora Llamada'} ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs">
                                        {{ $row->{'Hora Creacion'} ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs">
                                        {{ $row->{'Hora Despacho'} ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs">
                                        {{ $row->{'Hora En Sitio'} ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs">
                                        {{ $row->{'Hora Terminado'} ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs">
                                        {{ $row->{'Hora Cierre'} ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs font-bold">
                                        {{ $row->{'Tiempo Total'} ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <x-heroicon-m-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p>No se encontraron eventos con ese numero.</p>
                </div>
            @endif
        </div>
    @else
        {{-- Estado inicial: sin busqueda --}}
        <div class="text-center py-20 text-gray-500 dark:text-gray-400">
            <x-heroicon-m-document-text class="w-16 h-16 mx-auto mb-4 opacity-30" />
            <p class="text-lg font-medium mb-1">Selecciona un rango de fechas</p>
            <p class="text-sm">Usa los filtros de arriba para consultar los eventos del sistema CAD.</p>
        </div>
    @endif
</x-filament-panels::page>
