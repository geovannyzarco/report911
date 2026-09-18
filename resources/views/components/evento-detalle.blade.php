{{--
    Componente: evento-detalle
    Contenido del detalle de un evento del CAD (identificacion, ubicacion con mapa,
    contactos, personal, linea de tiempo, duraciones y cronologia de notas).
    Se reutiliza tanto en el modal del Reporte de Eventos (EventReportTable) como en el
    modal de la accion "Ver Evento" del widget de Incidentes Activos.

    Props:
    - detalleEvento: objeto con los datos del evento (ver CadReportService::getDetalleEvento)
    - notasEvento: arreglo de notas cronologicas (ver CadReportService::getNotasEvento)
--}}
@props(['detalleEvento', 'notasEvento'])

{{-- Seccion 1: Informacion completa del evento --}}
<div class="space-y-6">
    {{-- Identificacion y Categoria --}}
    <x-filament::section heading="Identificacion y Categoria" icon="heroicon-m-information-circle">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-500 dark:text-gray-400">Numero de Evento:</span>
                <p class="text-gray-900 dark:text-white font-bold">{{ $detalleEvento->{'Numero de Evento'} ?? '-' }}</p>
            </div>
            <div>
                <span class="font-medium text-gray-500 dark:text-gray-400">Numero de Incidente:</span>
                <p class="text-gray-900 dark:text-white font-bold">{{ $detalleEvento->{'Numero Incidente Formateado'} ?? '-' }}</p>
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

        {{-- Enlace a Google Maps con las coordenadas del evento (sin mapa embebido) --}}
        @if($detalleEvento->{'Coordenada X'} && $detalleEvento->{'Coordenada Y'})
            <div class="mt-4 flex items-center gap-2">
                <a href="https://www.google.com/maps?q={{ $detalleEvento->{'Coordenada Y'} }},{{ $detalleEvento->{'Coordenada X'} }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    <x-heroicon-o-map-pin class="w-4 h-4" />
                    Abrir en Google Maps
                </a>
            </div>
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
