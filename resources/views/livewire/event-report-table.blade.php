<div>
    @if($busquedaEjecutada)
        <div class="mt-4 mb-4 flex flex-wrap items-center justify-between gap-4">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Se encontraron <strong class="text-gray-900 dark:text-white">{{ number_format($this->results->total()) }}</strong> eventos
            </span>

            <select wire:model.live="perPage" class="fi-input w-auto rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="10">10 por pagina</option>
                <option value="25">25 por pagina</option>
                <option value="50">50 por pagina</option>
                <option value="100">100 por pagina</option>
            </select>
        </div>

        @if($this->results->count() > 0)
            <div class="fi-ta-ctn w-full overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <table class="fi-ta-table w-full text-start divide-y divide-gray-200 dark:divide-gray-700" style="table-layout: fixed; min-width: 1150px;">
                    <colgroup>
                        <col style="width: 40px;">
                        <col style="width: 170px;">
                        <col style="width: 150px;">
                        <col style="width: 130px;">
                        <col style="width: 130px;">
                        <col style="width: 75px;">
                        <col style="width: 75px;">
                        <col style="width: 75px;">
                        <col style="width: 75px;">
                        <col style="width: 75px;">
                        <col style="width: 75px;">
                        <col style="width: 75px;">
                    </colgroup>
                    <thead class="fi-ta-header bg-gray-50 dark:bg-gray-800/50">
                        <tr class="fi-ta-header-row border-b border-gray-200 dark:border-gray-700">
                            <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Evento</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipo</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Telefonista</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Despachador</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Llamada</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Creacion</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Despacho</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">En Sitio</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Terminado</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cierre</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tiempo</th>
                        </tr>
                    </thead>
                    <tbody class="fi-ta-body divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->results->items() as $index => $row)
                            <tr class="fi-ta-row bg-white transition-colors hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                                <td class="fi-ta-cell px-3 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $this->results->firstItem() + $index }}</td>
                                <td class="fi-ta-cell px-3 py-3 text-sm font-bold text-gray-950 dark:text-white whitespace-nowrap overflow-hidden text-ellipsis">{{ $row->{'Numero de Evento'} }}</td>
                                <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $row->{'Tipo de Evento'} }}">{{ $row->{'Tipo de Evento'} }}</td>
                                <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $row->Telefonista }}">{{ $row->Telefonista }}</td>
                                <td class="fi-ta-cell px-3 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $row->Despachador }}">{{ $row->Despachador }}</td>
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

            <div class="mt-4">
                {{ $this->results->links() }}
            </div>
        @else
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <p>No se encontraron eventos.</p>
            </div>
        @endif
    @else
        <div class="text-center py-20 text-gray-500 dark:text-gray-400">
            <p class="text-lg font-medium mb-1">Selecciona un rango de fechas</p>
            <p class="text-sm">Usa los filtros de arriba para consultar los eventos del sistema CAD.</p>
        </div>
    @endif
</div>
