<div>
    @if($busquedaEjecutada)
        <div class="mt-4 mb-4 flex flex-wrap items-center justify-between gap-4">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Se encontraron <strong class="text-gray-900 dark:text-white">{{ number_format($this->total()) }}</strong> eventos
            </span>

            <select wire:model.live="perPage" class="w-auto rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="10">10 por pagina</option>
                <option value="25">25 por pagina</option>
                <option value="50">50 por pagina</option>
                <option value="100">100 por pagina</option>
            </select>
        </div>

        @if($this->total() > 0)
            <div class="w-full overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <table class="w-full text-start divide-y divide-gray-200 dark:divide-gray-700 table-auto min-w-max">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">#</th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <button type="button" wire:click="sortBy('evento')" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                                    Evento
                                    @if($sortColumn === 'evento')
                                        @if($sortDirection === 'asc')
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Tipo</th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Telefonista</th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Despachador</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Llamada</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Creacion</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Despacho</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">En Sitio</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Terminado</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">Cierre</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <button type="button" wire:click="sortBy('tiempo')" class="inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                                    TIEMPO
                                    @if($sortColumn === 'tiempo')
                                        @if($sortDirection === 'asc')
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        @endif
                                    @endif
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->pagedResults() as $index => $row)
                            @php $num = ($currentPage - 1) * $perPage + $index + 1; @endphp
                            <tr class="bg-white transition-colors hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $num }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-950 dark:text-white whitespace-nowrap">{{ $row->{'Numero de Evento'} }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 min-w-[200px]">{{ $row->{'Tipo de Evento'} }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 min-w-[150px]">{{ $row->Telefonista }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 min-w-[150px]">{{ $row->Despachador }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Llamada'} ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Creacion'} ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Despacho'} ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora En Sitio'} ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Terminado'} ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $row->{'Hora Cierre'} ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-sm font-bold text-gray-950 dark:text-white whitespace-nowrap">{{ $row->{'Tiempo Total'} ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($this->totalPages() > 1)
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Pagina {{ $currentPage }} de {{ $this->totalPages() }}
                    </span>

                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="goToPage(1)" @disabled($currentPage === 1)
                            class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                            &laquo;
                        </button>
                        <button type="button" wire:click="previousPage" @disabled($currentPage === 1)
                            class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                            &lsaquo;
                        </button>

                        @php
                            $inicio = max(1, $currentPage - 2);
                            $fin = min($this->totalPages(), $currentPage + 2);
                        @endphp

                        @for($i = $inicio; $i <= $fin; $i++)
                            <button type="button" wire:click="goToPage({{ $i }})"
                                class="px-3 py-2 text-sm font-semibold border border-gray-300 rounded-lg transition
                                    {{ $i === $currentPage ? 'bg-primary-600 text-white border-primary-600 hover:bg-primary-500' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                                {{ $i }}
                            </button>
                        @endfor

                        <button type="button" wire:click="nextPage" @disabled($currentPage === $this->totalPages())
                            class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                            &rsaquo;
                        </button>
                        <button type="button" wire:click="goToPage({{ $this->totalPages() }})" @disabled($currentPage === $this->totalPages())
                            class="px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                            &raquo;
                        </button>
                    </div>
                </div>
            @endif
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
