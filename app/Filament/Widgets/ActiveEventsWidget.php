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

    // Intervalo de polling: cada 30 segundos se re-ejecuta la query via AJAX
    // y se reemplaza solo el HTML de este widget sin recargar la pagina
    protected static string|false $pollingInterval = '30s';

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
        // Incident::query() retorna un Eloquent Builder (requerido por Filament)
        // que apunta a la tabla 'Incidents' en la conexion 'sqlsrv_cad'
        $query = Incident::query()
            // Selecciona las columnas necesarias del incidente y sus relaciones
            ->select([
                'Incidents.OID',                  // Identificador unico del incidente
                'Incidents.SequenceNumber',       // Numero de ticket visible al usuario
                'Incidents.CreationTime',         // Fecha/hora de creacion del incidente
                'cl.Name as Clasificacion',       // Tipo de incidente (robo, accidente, etc.)
                'pr.Name as Prioridad',           // Nivel de prioridad (Normal, SEM, etc.)
                'st.Name as Estado',              // Estado actual del incidente
                'ag.Name as Agencia',             // Agencia primaria asignada
                DB::raw('COALESCE(a.DisplayName, a.LogonName) as Operador'),  // Nombre del operador
            ])
            // LEFT JOIN a la tabla de clasificaciones para obtener el nombre del tipo
            ->leftJoin('Classifications as cl', 'Incidents.Classification', '=', 'cl.OID')
            // LEFT JOIN a la tabla de prioridades para obtener el nombre
            ->leftJoin('Priorities as pr', 'Incidents.Priority', '=', 'pr.OID')
            // LEFT JOIN a la tabla de estados para obtener el nombre del status
            ->leftJoin('Statuses as st', 'Incidents.Status', '=', 'st.OID')
            // LEFT JOIN a la tabla de agencias para obtener el nombre de la agencia
            ->leftJoin('Agencies as ag', 'Incidents.PrimaryAgency', '=', 'ag.OID')
            // LEFT JOIN a la tabla de agentes/operadores para obtener su nombre
            ->leftJoin('Agents as a', 'Incidents.Agent', '=', 'a.OID')
            // Excluye status: 6=Terminado, 7=Cerrado, 8=Req_Despacho (solo muestra activos)
            ->whereNotIn('Incidents.Status', [6, 7, 8])
            // Filtra incidentes no eliminados (soft delete del CAD)
            ->where(function ($q) {
                $q->where('Incidents.Deleted', 0)->orWhereNull('Incidents.Deleted');
            })
            // Filtra solo incidentes de las ultimas 24 horas
            ->whereRaw("Incidents.CreationTime >= '$desde'");

        // Configura la tabla de Filament con la query y las columnas a mostrar
        return $table
            // Pasa la query Eloquent al widget (Filament la ejecuta y renderiza)
            ->query(fn () => $query)
            // Define las columnas de la tabla
            ->columns([
                // Columna 1: Numero de ticket del incidente (buscable)
                Tables\Columns\TextColumn::make('SequenceNumber')
                    ->label('Incidente')           // Titulo de la columna
                    ->searchable()                 // Permite buscar por este campo
                    ->weight('bold'),              // Texto en negrita

                // Columna 2: Hora de creacion del incidente
                Tables\Columns\TextColumn::make('CreationTime')
                    ->label('Hora')
                    ->dateTime('H:i:s')            // Muestra solo hora:minuto:segundo
                    ->sortable(),                  // Permite ordenar por esta columna

                // Columna 3: Estado del incidente con badge de color
                Tables\Columns\TextColumn::make('Estado')
                    ->label('Estado')
                    ->badge()                      // Muestra como badge/etiqueta con color
                    // Asigna color segun el estado del incidente
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'En Ruta') => 'warning',    // Amarillo: unidad en camino
                        str_contains($state, 'En Sitio') => 'danger',    // Rojo: unidad en el lugar
                        str_contains($state, 'Despachado') => 'info',    // Azul: unidad despachada
                        default => 'gray',                               // Gris: otros estados
                    }),

                // Columna 4: Clasificacion del incidente (max 30 caracteres)
                Tables\Columns\TextColumn::make('Clasificacion')
                    ->label('Clasificacion')
                    ->limit(30),                   // Trunca texto largo con "..."

                // Columna 5: Prioridad del incidente
                Tables\Columns\TextColumn::make('Prioridad')
                    ->label('Prioridad'),

                // Columna 6: Agencia primaria asignada (max 25 caracteres)
                Tables\Columns\TextColumn::make('Agencia')
                    ->label('Agencia')
                    ->limit(25),

                // Columna 7: Operador que atendio el incidente
                Tables\Columns\TextColumn::make('Operador')
                    ->label('Operador'),
            ])
            // Orden por defecto: los mas recientes primero
            ->defaultSort('CreationTime', 'desc')
            // Paginacion: muestra 10, 25 o 50 registros por pagina
            ->paginated([10, 25, 50])
            // Polling: cada 30 segundos re-ejecuta la query y actualiza solo esta tabla
            ->poll('30s');
    }
}
