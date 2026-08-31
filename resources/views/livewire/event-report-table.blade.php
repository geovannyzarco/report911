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
                            {{-- Fila clickeable: al hacer click abre el modal con los detalles del evento --}}
                            <tr wire:click="verDetalle('{{ $row->{'Numero de Evento'} }}')"
                                class="bg-white transition-colors hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50 cursor-pointer">
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

    {{-- Modal de detalles del evento: se abre al hacer clic en una fila --}}
    <x-filament::modal id="detalle-evento" width="7xl" :close-button="true">
        {{-- Encabezado del modal con el numero de evento --}}
        <x-slot name="heading">
            Detalle del Evento: {{ $detalleEvento->{'Numero de Evento'} ?? '' }}
        </x-slot>

        @if($detalleEvento)
            {{-- Seccion 1: Informacion completa del evento --}}
            <div class="space-y-6">
                {{-- Identificacion y Categoria --}}
                <x-filament::section heading="Identificacion y Categoria" icon="heroicon-m-information-circle">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Numero de Evento:</span>
                            <p class="text-gray-900 dark:text-white font-bold">{{ $detalleEvento->{'Numero de Evento'} }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Tipo de Evento:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Tipo de Evento'} }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Prioridad:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->Prioridad ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Agencia:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->Agencia ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Estado Actual:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Estado Actual'} }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Origen de Entrada:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Origen de Entrada'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Codigo de Cierre:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Codigo Cierre'} ?? 'Sin asignar' }}</p>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Ubicacion Geografica --}}
                <x-filament::section heading="Ubicacion Geografica" icon="heroicon-m-map-pin">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="md:col-span-3">
                            <span class="font-medium text-gray-500 dark:text-gray-400">Direccion Completa:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Direccion Completa'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Lugar Comun:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Lugar Comun'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Calle Principal:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Calle Principal'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Zona / Sector:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->Zona ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Cruce Calle 1:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Cruce Calle 1'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Cruce Calle 2:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Cruce Calle 2'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Coordenadas:</span>
                            <p class="text-gray-900 dark:text-white">X: {{ $detalleEvento->{'Coordenada X'} ?? '-' }} / Y: {{ $detalleEvento->{'Coordenada Y'} ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Mapa interactivo con Leaflet.js y OpenStreetMap --}}
                    @if($detalleEvento->{'Coordenada X'} && $detalleEvento->{'Coordenada Y'})
                        <div class="mt-4">
                            <div id="mapa-evento" class="w-full h-[400px] rounded-lg border border-gray-200 dark:border-gray-700 z-0"></div>
                            <div class="mt-2 flex items-center gap-2">
                                <a href="https://www.google.com/maps?q={{ $detalleEvento->{'Coordenada Y'} }},{{ $detalleEvento->{'Coordenada X'} }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                    <x-heroicon-o-map-pin class="w-4 h-4" />
                                    Abrir en Google Maps
                                </a>
                            </div>
                        </div>
                        <script>
                            // Inicializa el mapa de Leaflet con las coordenadas del evento
                            // Se ejecuta via Livewire event para garantizar que el DOM ya esta renderizado
                            document.addEventListener('livewire:initialized', function () {
                                Livewire.on('mapa-evento-listo', function () {
                                    setTimeout(function () {
                                        var el = document.getElementById('mapa-evento');
                                        if (!el || el._mapInit) return;
                                        el._mapInit = true;
                                        var lat = {{ $detalleEvento->{'Coordenada Y'} }};
                                        var lng = {{ $detalleEvento->{'Coordenada X'} }};
                                        if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) {
                                            el.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Coordenadas no disponibles</div>';
                                            return;
                                        }
                                        var mapa = L.map(el, { zoomControl: true }).setView([lat, lng], 16);
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '&copy; OpenStreetMap contributors',
                                            maxZoom: 19
                                        }).addTo(mapa);
                                        L.marker([lat, lng]).addTo(mapa)
                                            .bindPopup('<strong>{{ addslashes($detalleEvento->{'Numero de Evento'} ?? '') }}</strong><br>{{ addslashes($detalleEvento->{'Direccion Completa'} ?? '') }}')
                                            .openPopup();
                                        setTimeout(function () { mapa.invalidateSize(); }, 300);
                                    }, 150);
                                });
                            });
                        </script>
                    @endif
                </x-filament::section>

                {{-- Datos de Contacto (Informante) --}}
                <x-filament::section heading="Datos de Contacto (Informante)" icon="heroicon-m-phone">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Nombre del Informante:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Nombre Informante'} }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Telefono Informante:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Telefono Informante'} }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Tipo de Informante:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Tipo Informante'} ?? '-' }}</p>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Personal y WorkStations --}}
                <x-filament::section heading="Personal Asociado" icon="heroicon-m-users">
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Telefonista:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->Telefonista }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Usuario Telefonista:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Usuario Telefonista'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Puesto Telefonista:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Puesto Telefonista'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Despachador:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->Despachador }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Usuario Despachador:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Usuario Despachador'} ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Puesto Despachador:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Puesto Despachador'} ?? '-' }}</p>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Linea de Tiempo del Caso --}}
                <x-filament::section heading="Linea de Tiempo" icon="heroicon-m-clock">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Hora de Llamada:</span>
                            <p class="text-gray-900 dark:text-white font-bold">{{ $detalleEvento->{'Hora Llamada'} ? \Carbon\Carbon::parse($detalleEvento->{'Hora Llamada'})->format('H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Hora Creacion:</span>
                            <p class="text-gray-900 dark:text-white font-bold">{{ $detalleEvento->{'Hora Creacion'} ? \Carbon\Carbon::parse($detalleEvento->{'Hora Creacion'})->format('H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Hora Despachado:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Hora Despachado'} ? \Carbon\Carbon::parse($detalleEvento->{'Hora Despachado'})->format('H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Hora En Ruta:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Hora En Ruta'} ? \Carbon\Carbon::parse($detalleEvento->{'Hora En Ruta'})->format('H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Hora En Sitio:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Hora En Sitio'} ? \Carbon\Carbon::parse($detalleEvento->{'Hora En Sitio'})->format('H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Hora Terminado:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Hora Terminado'} ? \Carbon\Carbon::parse($detalleEvento->{'Hora Terminado'})->format('H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-500 dark:text-gray-400">Hora Cierre:</span>
                            <p class="text-gray-900 dark:text-white">{{ $detalleEvento->{'Hora Cierre'} ? \Carbon\Carbon::parse($detalleEvento->{'Hora Cierre'})->format('H:i:s') : '-' }}</p>
                        </div>
                    </div>

                    {{-- Duraciones calculadas --}}
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                            <span class="font-medium text-blue-600 dark:text-blue-400">Duracion Despacho (Ciem 911):</span>
                            <p class="text-blue-900 dark:text-blue-100 text-lg font-bold">{{ $detalleEvento->{'Duracion Despacho'} ?? '00:00:00' }}</p>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                            <span class="font-medium text-amber-600 dark:text-amber-400">Tiempo de Viaje (Respuesta):</span>
                            <p class="text-amber-900 dark:text-amber-100 text-lg font-bold">{{ $detalleEvento->{'Tiempo Viaje'} ?? '00:00:00' }}</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                            <span class="font-medium text-red-600 dark:text-red-400">Duracion Evento (Crea a Cierre):</span>
                            <p class="text-red-900 dark:text-red-100 text-lg font-bold">{{ $detalleEvento->{'Duracion Evento'} ?? '00:00:00' }}</p>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Seccion 2: Cronologia de Notas --}}
                <x-filament::section heading="Cronologia de Notas" icon="heroicon-m-document-text">
                    @if(count($notasEvento) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Fecha y Hora</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Operador / Agente</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Estacion</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Nota</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($notasEvento as $indice => $nota)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $indice + 1 }}</td>
                                            <td class="px-3 py-2 text-gray-900 dark:text-white whitespace-nowrap font-medium">
                                                {{ \Carbon\Carbon::parse($nota->{'Fecha y Hora'})->format('d/m/Y H:i:s') }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $nota->Operador }}</td>
                                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $nota->Estacion ?? '-' }}</td>
                                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400 max-w-lg">
                                                <div class="whitespace-pre-wrap text-xs leading-relaxed">{{ $nota->Nota }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No hay notas registradas para este evento.</p>
                    @endif
                </x-filament::section>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Cargando informacion del evento...</p>
        @endif
    </x-filament::modal>
</div>
