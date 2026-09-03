<?php

namespace App\Filament\Widgets;

use App\Models\Cad\Incident;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Widget: ActiveEventsWidget
 * Nombre: Incidentes Activos sin Cerrar
 * Descripcion: Muestra los incidentes activos ordenados por tiempo abierto (mayor primero).
 * Formato del numero de evento: SE911:AAAA:MM:DD:NNNN
 * Columnas: Evento, Tipo de Evento, Hora Creacion, Estado, Tiempo Transcurrido.
 */
class ActiveEventsWidget extends BaseWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    protected static ?string $heading = 'Incidentes Activos sin Cerrar';

    protected static ?int $sort = 4;

    // Polling desactivado: la query sobre 6M+ filas es costosa
    protected static string|false $pollingInterval = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Consulta raw con NOLOCK para evitar bloqueos contra el CAD
        // LEFT JOIN a Responses/ResponseTypes para obtener el Tipo de Evento
        // COALESCE toma el primer ResponseType disponible del incidente
        // DATEDIFF calcula segundos transcurridos desde creacion hasta ahora (GETDATE)
        // ORDER BY CreationTime ASC: los mas antiguos primero (mas tiempo sin cerrar)
        $hoy = Carbon::today()->format('Ymd');

        $query = Incident::query()
            ->select([
                'Incidents.OID',
                'Incidents.SequenceNumber',
                'Incidents.CreationTime',
                'st.Name as Estado',
                DB::raw("COALESCE((SELECT TOP 1 rt.Name FROM Responses r2 WITH (NOLOCK) INNER JOIN ResponseTypes rt WITH (NOLOCK) ON r2.ResponseType = rt.OID WHERE r2.Incident = Incidents.OID), 'Sin Tipo') as TipoEvento"),
                DB::raw('DATEDIFF(second, Incidents.CreationTime, GETDATE()) as SegundosTranscurridos'),
            ])
            ->leftJoin('Statuses as st', 'Incidents.Status', '=', 'st.OID')
            ->whereNotIn('Incidents.Status', [6, 7, 8])
            ->where(function ($q) {
                $q->where('Incidents.Deleted', 0)->orWhereNull('Incidents.Deleted');
            })
            ->whereRaw("Incidents.CreationTime >= '$hoy'")
            ->orderByRaw('Incidents.CreationTime ASC');

        return $table
            ->query(fn() => $query)
            ->columns([
                // Columna 1: Numero de evento formateado SE911:AAAA:MM:DD:NNNN
                // SequenceNumber de la DB es compound (ej: "00:25:277737")
                // Se extrae la parte numerica final (277737) y se muestra con formato SE911
                Tables\Columns\TextColumn::make('SequenceNumber')
                    ->label('Evento')
                    ->searchable()
                    ->weight('bold')
                    ->formatStateUsing(function ($state, $record): string {
                        $date = Carbon::parse($record->CreationTime);
                        // Extrae la ultima parte numerica del SequenceNumber compound
                        $parts = explode(':', $state);
                        $numero = end($parts);

                        return "SE911:{$date->format('Y:m:d')}:{$numero}";
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
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, 'En Ruta') => 'warning',
                        str_contains($state, 'En Sitio') => 'danger',
                        str_contains($state, 'Despachado') => 'info',
                        default => 'gray',
                    }),

                // Columna 5: Tiempo transcurrido formateado en 00:00:00
                Tables\Columns\TextColumn::make('SegundosTranscurridos')
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
            // Sin paginacion: muestra todos los activos del dia
            ->paginated([5, 10, 50, 100]);
    }
}
