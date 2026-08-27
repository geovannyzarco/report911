<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Page: EventReport
 * Nombre: Reporte de Eventos
 * Descripcion: Pagina personalizada que muestra un reporte detallado de eventos
 * del sistema CAD con tabla nativa de Filament.
 */
class EventReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.pages.event-report';

    protected static ?string $title = 'Reporte de Eventos';

    protected static ?string $navigationLabel = 'Reporte de Eventos';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-m-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Reportes';
    }

    /**
     * Fecha desde para el filtro.
     */
    public ?string $fechaDesde = null;

    /**
     * Fecha hasta para el filtro.
     */
    public ?string $fechaHasta = null;

    /**
     * Termino de busqueda por numero de evento.
     */
    public string $busqueda = '';

    /**
     * Resultados crudos de la consulta SQL.
     */
    public array $resultados = [];

    /**
     * Indica si se ha ejecutado una busqueda.
     */
    public bool $busquedaEjecutada = false;

    /**
     * Configuracion del formulario de filtros.
     */
    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Filtros de Busqueda')
                    ->description('Selecciona un rango de fechas para consultar los eventos')
                    ->icon('heroicon-m-funnel')
                    ->schema([
                        DatePicker::make('fechaDesde')
                            ->label('Fecha Desde')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->placeholder('Seleccionar fecha inicio'),

                        DatePicker::make('fechaHasta')
                            ->label('Fecha Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->placeholder('Seleccionar fecha fin'),

                        Forms\Components\TextInput::make('busqueda')
                            ->label('Buscar por Numero de Evento')
                            ->placeholder('Ej: 279215')
                            ->prefixIcon('heroicon-m-magnifying-glass'),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * Configuracion de la tabla nativa de Filament.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->resultados)
            ->columns([
                TextColumn::make('row_num')
                    ->label('#')
                    ->state(fn ($row) => $row->row_num ?? '-'),

                TextColumn::make('Numero de Evento')
                    ->label('Evento')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('Tipo de Evento')
                    ->label('Tipo')
                    ->limit(25)
                    ->tooltip(fn ($row) => $row->{'Tipo de Evento'}),

                TextColumn::make('Telefonista')
                    ->limit(20)
                    ->tooltip(fn ($row) => $row->Telefonista),

                TextColumn::make('Despachador')
                    ->limit(20)
                    ->tooltip(fn ($row) => $row->Despachador),

                TextColumn::make('Hora Llamada')
                    ->label('Llamada')
                    ->time('H:i:s'),

                TextColumn::make('Hora Creacion')
                    ->label('Creacion')
                    ->time('H:i:s'),

                TextColumn::make('Hora Despacho')
                    ->label('Despacho')
                    ->time('H:i:s'),

                TextColumn::make('Hora En Sitio')
                    ->label('En Sitio')
                    ->time('H:i:s'),

                TextColumn::make('Hora Terminado')
                    ->label('Terminado')
                    ->time('H:i:s'),

                TextColumn::make('Hora Cierre')
                    ->label('Cierre')
                    ->time('H:i:s'),

                TextColumn::make('Tiempo Total')
                    ->label('Tiempo')
                    ->weight('bold')
                    ->sortable(),
            ])
            ->defaultSort('Numero de Evento')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->searchable(false)
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    /**
     * Ejecuta la consulta SQL con los filtros de fecha.
     */
    public function buscar(): void
    {
        $this->validate([
            'fechaDesde' => 'required|date',
            'fechaHasta' => 'required|date|after_or_equal:fechaDesde',
        ]);

        $desde = Carbon::parse($this->fechaDesde)->startOfDay()->format('Ymd H:i:s');
        $hasta = Carbon::parse($this->fechaHasta)->endOfDay()->format('Ymd H:i:s');

        $query = "
            WITH cte_Calls AS (
                SELECT
                    c.Incident,
                    MIN(c.CreationTime) AS [HORA_LLAMADA_calls]
                FROM Calls c WITH (NOLOCK)
                WHERE c.Incident IN (
                    SELECT DISTINCT Incident
                    FROM Responses WITH (NOLOCK)
                    WHERE CreationTime BETWEEN '$desde' AND '$hasta'
                )
                GROUP BY c.Incident
            ),
            cte_LlamadaEvento AS (
                SELECT
                    a.Incident,
                    a.ResponseType,
                    a.SequenceNumber AS [NUMERO_SECUENCIA],
                    a.OID AS ResponseOID,
                    g.Name AS [TIPO_RESPUESTA],
                    d.[HORA_LLAMADA_calls],
                    a.CreationTime AS [FECHA_CREACION_EVENTO_responses],
                    i.Agent AS IncidentAgentOID,
                    CASE WHEN a.Status = 7 THEN a.StatusTime ELSE NULL END AS HoraCierre
                FROM Responses AS a WITH (NOLOCK)
                INNER JOIN cte_Calls AS d ON d.Incident = a.Incident
                INNER JOIN Incidents AS i WITH (NOLOCK) ON a.Incident = i.OID
                INNER JOIN ResponseTypes AS g WITH (NOLOCK) ON g.OID = a.ResponseType
                WHERE a.CreationTime BETWEEN '$desde' AND '$hasta'
            ),
            cte_tiempos_fases AS (
                SELECT
                    a.ResponseOID,
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.StatusTime END) AS [Despachado],
                    MAX(CASE WHEN c.Name = 'En Ruta' THEN am.StatusTime END) AS [En Ruta],
                    MAX(CASE WHEN c.Name = 'En Sitio' THEN am.StatusTime END) AS [En Sitio],
                    MAX(CASE WHEN c.Name = 'Terminado' THEN am.StatusTime END) AS [Terminado],
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.Agent END) AS DespachadorAgentOID
                FROM cte_LlamadaEvento a
                INNER JOIN AssignModif am WITH (NOLOCK) ON am.Response = a.ResponseOID
                INNER JOIN Statuses c WITH (NOLOCK) ON c.OID = am.ResourceStatus
                GROUP BY a.ResponseOID
            ),
            cte_final AS (
                SELECT
                    ROW_NUMBER() OVER (ORDER BY le.[NUMERO_SECUENCIA]) AS [row_num],
                    le.[NUMERO_SECUENCIA] AS [Numero de Evento],
                    le.[TIPO_RESPUESTA] AS [Tipo de Evento],
                    COALESCE(ag_tel.Firstname + ' ' + ag_tel.Lastname, 'Desconocido') AS [Telefonista],
                    COALESCE(ag_dsp.Firstname + ' ' + ag_dsp.Lastname, 'Desconocido') AS [Despachador],
                    CAST(le.[HORA_LLAMADA_calls] AS TIME(0)) AS [Hora Llamada],
                    CAST(le.[FECHA_CREACION_EVENTO_responses] AS TIME(0)) AS [Hora Creacion],
                    CAST(tf.[Despachado] AS TIME(0)) AS [Hora Despacho],
                    CAST(tf.[En Sitio] AS TIME(0)) AS [Hora En Sitio],
                    CAST(tf.[Terminado] AS TIME(0)) AS [Hora Terminado],
                    CAST(le.HoraCierre AS TIME(0)) AS [Hora Cierre],
                    CONVERT(VARCHAR(8),
                        DATEADD(SECOND,
                            CASE
                                WHEN le.HoraCierre >= le.[FECHA_CREACION_EVENTO_responses]
                                THEN DATEDIFF(SECOND, le.[FECHA_CREACION_EVENTO_responses], le.HoraCierre)
                                ELSE 0
                            END,
                        0),
                    108) AS [Tiempo Total]
                FROM cte_LlamadaEvento le
                LEFT JOIN cte_tiempos_fases tf ON le.ResponseOID = tf.ResponseOID
                LEFT JOIN Agents ag_tel WITH (NOLOCK) ON le.IncidentAgentOID = ag_tel.OID
                LEFT JOIN Agents ag_dsp WITH (NOLOCK) ON tf.DespachadorAgentOID = ag_dsp.OID
            )
            SELECT * FROM cte_final
            WHERE ([Numero de Evento] LIKE '%$this->busqueda%' OR '$this->busqueda' = '')
            ORDER BY [Numero de Evento]
        ";

        $this->resultados = DB::connection('sqlsrv_cad')->select($query);
        $this->busquedaEjecutada = true;

        $this->resetTablePage();
    }

    /**
     * Resetea la busqueda al cambiar el termino.
     */
    public function updatedBusqueda(): void
    {
        $this->resetTablePage();
    }
}
