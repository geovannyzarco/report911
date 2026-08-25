<?php

namespace App\Filament\Widgets;

use App\Models\Cad\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget: FieldResourcesWidget
 * Nombre: Unidades en Campo
 * Descripcion: Muestra una tabla con todas las unidades/recursos que actualmente
 * tienen un despacho activo (ActiveResponse != 0). Incluye: codigo de la unidad,
 * estado actual, estacion base, numero de incidente asignado y tipo de respuesta.
 * Usa el modelo Eloquent Resource (connection: sqlsrv_cad) con JOINs a las
 * tablas de catalogo (Statuses, Stations, Responses, Incidents, ResponseTypes).
 * Se refresca automaticamente cada 30 segundos via polling de Filament.
 */
class FieldResourcesWidget extends BaseWidget
{
    // Titulo del widget que se muestra en el dashboard
    protected static ?string $heading = 'Unidades en Campo';

    // Orden de visualizacion en el dashboard
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
        // Construye la query Eloquent usando el modelo Resource
        // Resource::query() retorna un Eloquent Builder que apunta a la tabla
        // 'Resources' en la conexion 'sqlsrv_cad' (SQL Server del CAD)
        $query = Resource::query()
            // Selecciona las columnas necesarias del recurso y sus relaciones
            ->select([
                'Resources.OID',                  // Identificador unico del recurso/unidad
                'Resources.Name as CodigoUnidad', // Codigo de la unidad (ej: PR-10, AMB-02)
                'st.Name as EstadoUnidad',        // Estado actual de la unidad
                'sta.Name as Estacion',           // Estacion base de procedencia
                'i.SequenceNumber as Incidente',  // Numero de ticket del incidente asignado
                'rt.Name as TipoRespuesta',       // Tipo de despacho (accidente, incendio, etc.)
            ])
            // LEFT JOIN a la tabla de estados para obtener el nombre del status de la unidad
            // StatusType 2 = estados de recursos (Disponible, Despachado, En Ruta, etc.)
            ->leftJoin('Statuses as st', 'Resources.Status', '=', 'st.OID')
            // LEFT JOIN a la tabla de estaciones para obtener el nombre de la base
            ->leftJoin('Stations as sta', 'Resources.Station', '=', 'sta.OID')
            // LEFT JOIN a la tabla de respuestas/despachos para obtener datos del despacho activo
            ->leftJoin('Responses as r', 'Resources.ActiveResponse', '=', 'r.OID')
            // LEFT JOIN a la tabla de incidentes para obtener el numero de ticket
            ->leftJoin('Incidents as i', 'Resources.ActiveIncident', '=', 'i.OID')
            // LEFT JOIN a la tabla de tipos de respuesta para obtener la descripcion
            ->leftJoin('ResponseTypes as rt', 'r.ResponseType', '=', 'rt.OID')
            // Filtra solo unidades que tienen un despacho activo (ActiveResponse no es 0)
            ->where('Resources.ActiveResponse', '!=', 0)
            // Filtra que ActiveResponse no sea nulo (unidad sin despacho)
            ->whereNotNull('Resources.ActiveResponse');

        // Configura la tabla de Filament con la query y las columnas a mostrar
        return $table
            // Pasa la query Eloquent al widget (Filament la ejecuta y renderiza)
            ->query(fn () => $query)
            // Define las columnas de la tabla
            ->columns([
                // Columna 1: Codigo/nombre de la unidad (buscable)
                Tables\Columns\TextColumn::make('CodigoUnidad')
                    ->label('Unidad')              // Titulo de la columna
                    ->searchable()                 // Permite buscar por este campo
                    ->weight('bold'),              // Texto en negrita

                // Columna 2: Estado actual de la unidad con badge de color
                Tables\Columns\TextColumn::make('EstadoUnidad')
                    ->label('Estado')
                    ->badge()                      // Muestra como badge/etiqueta con color
                    // Asigna color segun el estado de la unidad
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Despachado') => 'warning',   // Amarillo: unidad despachada
                        str_contains($state, 'En Ruta') => 'info',         // Azul: unidad en camino
                        str_contains($state, 'En Sitio') => 'success',     // Verde: unidad en el lugar
                        str_contains($state, 'Fuera De Turno') => 'gray',  // Gris: fuera de servicio
                        default => 'gray',                                  // Gris: otros estados
                    }),

                // Columna 3: Estacion base de la unidad (max 30 caracteres)
                Tables\Columns\TextColumn::make('Estacion')
                    ->label('Estacion')
                    ->limit(30),                   // Trunca texto largo con "..."

                // Columna 4: Numero de ticket del incidente asignado
                Tables\Columns\TextColumn::make('Incidente')
                    ->label('Incidente'),

                // Columna 5: Tipo de respuesta/despacho (max 35 caracteres)
                Tables\Columns\TextColumn::make('TipoRespuesta')
                    ->label('Tipo Respuesta')
                    ->limit(35),                   // Trunca texto largo con "..."
            ])
            // Orden por defecto: alfabeticamente por codigo de unidad
            ->defaultSort('CodigoUnidad')
            // Paginacion: muestra 10, 25 o 50 registros por pagina
            ->paginated([10, 25, 50])
            // Polling: cada 30 segundos re-ejecuta la query y actualiza solo esta tabla
            ->poll('30s');
    }
}
