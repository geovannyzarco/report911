<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Page: EventReport
 * Nombre: Reporte de Eventos
 * Descripcion: Pagina personalizada que muestra un reporte detallado de eventos
 * del sistema CAD. Incluye filtros de fecha/hora y busqueda por numero de evento.
 * La tabla inicia vacia y solo carga datos cuando el usuario selecciona un rango de fechas.
 */
class EventReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Reporte de Eventos';

    protected static ?string $navigationLabel = 'Reporte de Eventos';

    protected static ?int $navigationSort = 1;

    /**
     * Icono de navegacion en el sidebar.
     */
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-m-document-text';
    }

    /**
     * Grupo de navegacion en el sidebar.
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Reportes';
    }

    /**
     * Retorna la vista Blade para esta pagina.
     */
    public function render(): View
    {
        return view('filament.pages.event-report');
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
     * Resultados de la consulta.
     */
    public array $resultados = [];

    /**
     * Total de registros encontrados.
     */
    public int $totalRegistros = 0;

    /**
     * Termino de busqueda por numero de evento.
     */
    public string $busqueda = '';

    /**
     * Indica si se ha ejecutado una busqueda.
     */
    public bool $busquedaEjecutada = false;

    /**
     * Configuracion del formulario de filtros.
     */
    public function form(Form $form): Form
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
            )
            SELECT
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
            ORDER BY le.[NUMERO_SECUENCIA]
        ";

        $this->resultados = DB::connection('sqlsrv_cad')->select($query);
        $this->totalRegistros = count($this->resultados);
        $this->busquedaEjecutada = true;
    }

    /**
     * Obtiene los resultados filtrados por busqueda de numero de evento.
     */
    public function getResultadosFiltrados(): array
    {
        if (empty($this->busqueda)) {
            return $this->resultados;
        }

        return array_filter($this->resultados, function ($row) {
            return str_contains($row->{'Numero de Evento'}, $this->busqueda);
        });
    }
}
