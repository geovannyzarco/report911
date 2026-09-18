<?php

namespace App\Filament\Widgets;

use App\Models\Cad\Incident;
use App\Services\CadReportService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Widget: ActiveEventsWidget
 * Nombre: Incidentes Activos sin Cerrar
 * Descripcion: Muestra los incidentes activos ordenados por tiempo (el de mayor duracion primero).
 * Formato del numero de evento: SE911:AAAA:MM:DD:NNNN (numero del ultimo response del incidente).
 * Columnas: Evento, Tipo de Evento, Hora Creacion, Estado, Tiempo (duracion cerrada, igual al reporte).
 * Accion de fila: redirige al Reporte de Eventos con el numero del ultimo response pre-cargado.
 */
class ActiveEventsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Incidentes con mayor duracion de tiempo antes de cerrarlos';

    protected static ?int $sort = 4;

    // Polling cada 60s: re-ejecuta la query para reflejar incidentes nuevos/cerrados
    // (medicion 2026-09-08: ~0.22-0.38s por ejecucion sobre las tablas del CAD)
    protected static string|false $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    /** @var object|null Detalle del evento que se muestra en el modal "Ver Evento" */
    public ?object $detalleEvento = null;

    /** @var array Notas cronologicas del evento que se muestra en el modal "Ver Evento" */
    public array $notasEvento = [];

    public function table(Table $table): Table
    {
        // Consulta raw con NOLOCK para evitar bloqueos contra el CAD
        // LEFT JOIN a Responses/ResponseTypes para obtener el Tipo de Evento
        // COALESCE toma el primer ResponseType disponible del incidente
        // El numero (NumeroResponse) y el estado provienen del ULTIMO response del
        // incidente (mas reciente por CreationTime/OID): es la respuesta vigente.
        // La columna SegundosDuracion es la duracion cerrada del primer response
        // (mismo calculo que el "Tiempo Total" del reporte de eventos)
        // ORDER BY SegundosDuracion DESC: el evento con mas tiempo primero;
        // como desempate CreationTime ASC (mas antiguo primero), tal como se indica
        // en la cabecera del widget ("ordenados por tiempo, mayor primero")
        $hoy = Carbon::today()->format('Ymd');

        $query = Incident::query()
            ->select([
                'Incidents.OID',
                'Incidents.CreationTime',
                DB::raw("COALESCE((SELECT TOP 1 rt.Name FROM Responses r2 WITH (NOLOCK) INNER JOIN ResponseTypes rt WITH (NOLOCK) ON r2.ResponseType = rt.OID WHERE r2.Incident = Incidents.OID), 'Sin Tipo') as TipoEvento"),
                // Numero de evento: SE911 del ultimo response del incidente (response vigente)
                DB::raw('(SELECT TOP 1 r1.SequenceNumber FROM Responses r1 WITH (NOLOCK) WHERE r1.Incident = Incidents.OID ORDER BY r1.CreationTime DESC, r1.OID DESC) as NumeroResponse'),
                // Estado: nombre del status del ultimo response del incidente
                DB::raw('(SELECT TOP 1 s1.Name FROM Responses r5 WITH (NOLOCK) INNER JOIN Statuses s1 WITH (NOLOCK) ON r5.Status = s1.OID WHERE r5.Incident = Incidents.OID ORDER BY r5.CreationTime DESC, r5.OID DESC) as Estado'),
                // Duracion cerrada del primer response del incidente, igual que el campo
                // "Tiempo Total" del reporte de eventos. Si el response esta cerrado
                // (Status = 7), es la diferencia entre su creacion y su cierre; si no, 0.
                // Reemplaza al anterior "tiempo transcurrido en vivo" (DATEDIFF vs GETDATE)
                // para que el widget coincida exactamente con lo que muestra el reporte.
                DB::raw('ISNULL((SELECT TOP 1
                        CASE WHEN r.Status = 7 AND r.StatusTime >= r.CreationTime
                             THEN DATEDIFF(SECOND, r.CreationTime, r.StatusTime) ELSE 0 END
                     FROM Responses r WITH (NOLOCK)
                     WHERE r.Incident = Incidents.OID
                     ORDER BY r.CreationTime, r.OID), 0) as SegundosDuracion'),
            ])
            // El filtro de "activo" se mantiene a nivel de incidente (estados 6/7/8 = cerrado/terminado)
            ->whereNotIn('Incidents.Status', [6, 7, 8])
            ->where(function ($q) {
                $q->where('Incidents.Deleted', 0)->orWhereNull('Incidents.Deleted');
            })
            ->whereRaw("Incidents.CreationTime >= '$hoy'")
            ->orderBy('SegundosDuracion', 'desc')
            ->orderByRaw('Incidents.CreationTime ASC');

        return $table
            ->query(fn () => $query)
            ->columns([
                // Columna 1: Numero del ultimo response (SE911:AAAA:MM:DD:NNNN) del incidente
                // Ya viene con el formato SE911, no se reformatea. La busqueda se hace
                // sobre Responses.SequenceNumber (la columna origen es un alias SQL,
                // no se puede buscar directamente).
                Tables\Columns\TextColumn::make('NumeroResponse')
                    ->label('Evento')
                    ->weight('bold')
                    ->placeholder('Sin response')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereExists(function (Builder $subconsulta) use ($search): void {
                            $subconsulta->selectRaw('1')
                                ->from('Responses as respBusqueda')
                                ->whereColumn('respBusqueda.Incident', 'Incidents.OID')
                                ->where('respBusqueda.SequenceNumber', 'like', '%'.$search.'%');
                        });
                    }),

                // Columna 2: Tipo de evento (ResponseType)
                Tables\Columns\TextColumn::make('TipoEvento')
                    ->label('Tipo de Evento')
                    ->limit(40),

                // Columna 3: Fecha/hora de creacion del incidente
                Tables\Columns\TextColumn::make('CreationTime')
                    ->label('Creacion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                // Columna 4: Estado del incidente con badge de color
                Tables\Columns\TextColumn::make('Estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'En Ruta') => 'warning',
                        str_contains($state, 'En Sitio') => 'danger',
                        str_contains($state, 'Despachado') => 'info',
                        default => 'gray',
                    }),

                // Columna 5: Duracion del response (igual al reporte) formateada en 00:00:00
                Tables\Columns\TextColumn::make('SegundosDuracion')
                    ->label('Tiempo')
                    ->formatStateUsing(function ($state): string {
                        $seconds = (int) $state;
                        // Si por algún motivo de zona horaria da negativo, lo marcamos en 0.
                        if ($seconds < 0) {
                            $seconds = 0;
                        }

                        $hours = floor($seconds / 3600);
                        $minutes = floor(($seconds % 3600) / 60);
                        $secs = $seconds % 60;

                        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
                    })
                    ->color(function ($state): string {
                        $seconds = (int) $state;
                        if ($seconds >= 7200) { // 2 horas
                            return 'danger';
                        }
                        if ($seconds >= 3600) { // 1 hora
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->weight('bold'),
            ])
            ->actions([
                // Accion de fila: muestra un modal con el detalle completo del evento
                // (mismas secciones y notas que el modal del Reporte de Eventos).
                // Al montar la accion se cargan los datos desde CadReportService (cache 10 min).
                Action::make('ver_evento')
                    ->label('Ver Evento')
                    ->icon('heroicon-m-document-text')
                    ->color('primary')
                    // Sin response vigente no hay numero con el que consultar el detalle
                    ->disabled(fn ($record): bool => blank($record->NumeroResponse))
                    ->modal()
                    ->modalWidth('7xl')
                    ->modalHeading(fn (): string => 'Detalle del Evento: '.(
                        $this->detalleEvento?->{'Numero de Evento'}
                        ?? $this->detalleEvento?->{'Numero Incidente Formateado'}
                        ?? ''
                    ))
                    // El modal solo muestra informacion: sin boton de submit, solo cerrar
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->mountUsing(function (Action $action, $record = null): void {
                        // Numero del response vigente (la columna Evento del widget)
                        $numeroEvento = $record?->NumeroResponse ?? '';

                        if (blank($numeroEvento)) {
                            return;
                        }

                        // Detalle + notas desde el servicio compartido con el reporte
                        $servicio = new CadReportService;
                        $this->detalleEvento = $servicio->getDetalleEvento($numeroEvento);
                        $this->notasEvento = $servicio->getNotasEvento($numeroEvento);
                    })
                    ->modalContent(fn () => view('components.evento-detalle', [
                        'detalleEvento' => $this->detalleEvento,
                        'notasEvento' => $this->notasEvento,
                    ])),
            ])
            // Sin paginacion: muestra todos los activos del dia
            ->paginated([5, 10, 50, 100]);
    }
}
