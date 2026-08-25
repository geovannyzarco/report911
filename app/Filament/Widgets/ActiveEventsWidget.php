<?php

namespace App\Filament\Widgets;

use App\Models\Cad\Incident;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

/**
 * Widget: ActiveEventsWidget
 * Nombre: Incidentes Activos (Ultimas 24h)
 * Descripcion: Muestra una tabla con todos los incidentes de las ultimas 24 horas
 * que estan activos (no Terminados, no Cerrados, no en cola Req_Despacho).
 * Cada fila muestra: numero de incidente, hora, estado con badge de color,
 * clasificacion, prioridad, agencia y operador asignado.
 * Usa el modelo Eloquent Incident (connection: sqlsrv_cad) con JOINs a las
 * tablas de catalogo (Statuses, Classifications, Priorities, Agencies, Agents).
 * Se refresca automaticamente cada 30 segundos via polling de Filament.
 */
class ActiveEventsWidget extends BaseWidget
{
    // Titulo del widget que se muestra en el dashboard
    protected static ?string $heading = 'Incidentes Activos (Ultimas 24h)';

    // Orden de visualizacion en el dashboard (1=primer widget)
    protected static ?int $sort = 3;

    // Polling desactivado: la query sobre 6M+ filas de Incidents es demasiado lenta
    // La tabla se carga una vez y se actualiza al recargar el dashboard manualmente
    protected static string|false $pollingInterval = false;

    /**
     * Define la estructura de la tabla del widget.
     * Retorna un objeto Table configurado con columnas, query y opciones.
     *
     * @param  Table  $table  Instancia de Filament Table para configurar
     * @return Table Table configurada con query, columnas y paginacion
     */
    public function table(Table $table): Table
    {
        // Calcula la fecha de hace 24 horas en formato YYYYMMDD
        // Este formato es el unico que funciona con SQL Server en espanol
        $desde = Carbon::now()->subDay()->format('Ymd');

        // Construye la query Eloquent usando el modelo Incident
        // Simplificada: solo JOIN a Statuses para mostrar el estado
        $query = Incident::query()
            ->select([
                'Incidents.OID',
                'Incidents.SequenceNumber',
                'Incidents.CreationTime',
                'st.Name as Estado',
            ])
            ->leftJoin('Statuses as st', 'Incidents.Status', '=', 'st.OID')
            ->whereNotIn('Incidents.Status', [6, 7, 8])
            ->where(function ($q) {
                $q->where('Incidents.Deleted', 0)->orWhereNull('Incidents.Deleted');
            })
            ->whereRaw("Incidents.CreationTime >= '$desde'");

        // Configura la tabla de Filament con la query y las columnas a mostrar
        return $table
            // Pasa la query Eloquent al widget (Filament la ejecuta y renderiza)
            ->query(fn () => $query)
            // Define las columnas de la tabla
            ->columns([
                // Columna 1: Numero de ticket del incidente (buscable)
                Tables\Columns\TextColumn::make('SequenceNumber')
                    ->label('Incidente')
                    ->searchable()
                    ->weight('bold'),

                // Columna 2: Hora de creacion del incidente
                Tables\Columns\TextColumn::make('CreationTime')
                    ->label('Hora')
                    ->dateTime('H:i:s')
                    ->sortable(),

                // Columna 3: Estado del incidente con badge de color
                Tables\Columns\TextColumn::make('Estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'En Ruta') => 'warning',
                        str_contains($state, 'En Sitio') => 'danger',
                        str_contains($state, 'Despachado') => 'info',
                        default => 'gray',
                    }),
            ])
            // Orden por defecto: los mas recientes primero
            ->defaultSort('CreationTime', 'desc')
            // Paginacion: muestra 10, 25 o 50 registros por pagina
            ->paginated([10, 25, 50])
            // Polling: cada 30 segundos re-ejecuta la query y actualiza solo esta tabla
            ->poll('30s');
    }
}
