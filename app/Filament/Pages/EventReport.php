<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Page: EventReport
 * Nombre: Reporte de Eventos
 * Descripcion: Pagina personalizada que muestra un reporte detallado de eventos del sistema CAD.
 */
class EventReport extends Page implements HasForms
{
    use InteractsWithForms;

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

    public ?string $fechaDesde = null;

    public ?string $fechaHasta = null;

    public string $busqueda = '';

    public array $resultados = [];

    public bool $busquedaEjecutada = false;

    public int $paginaActual = 1;

    public int $porPagina = 25;

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
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('fechaHasta')
                            ->label('Fecha Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\TextInput::make('busqueda')
                            ->label('Buscar por Numero de Evento')
                            ->placeholder('Ej: 279215')
                            ->prefixIcon('heroicon-m-magnifying-glass'),
                    ])
                    ->columns(3),
            ]);
    }

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
                SELECT c.Incident, MIN(c.CreationTime) AS [HORA_LLAMADA_calls]
                FROM Calls c WITH (NOLOCK)
                WHERE c.Incident IN (
                    SELECT DISTINCT Incident FROM Responses WITH (NOLOCK)
                    WHERE CreationTime BETWEEN '$desde' AND '$hasta'
                )
                GROUP BY c.Incident
            ),
            cte_LlamadaEvento AS (
                SELECT a.Incident, a.ResponseType, a.SequenceNumber AS [NUMERO_SECUENCIA],
                    a.OID AS ResponseOID, g.Name AS [TIPO_RESPUESTA], d.[HORA_LLAMADA_calls],
                    a.CreationTime AS [FECHA_CREACION], i.Agent AS IncidentAgentOID,
                    CASE WHEN a.Status = 7 THEN a.StatusTime ELSE NULL END AS HoraCierre
                FROM Responses AS a WITH (NOLOCK)
                INNER JOIN cte_Calls AS d ON d.Incident = a.Incident
                INNER JOIN Incidents AS i WITH (NOLOCK) ON a.Incident = i.OID
                INNER JOIN ResponseTypes AS g WITH (NOLOCK) ON g.OID = a.ResponseType
                WHERE a.CreationTime BETWEEN '$desde' AND '$hasta'
            ),
            cte_tiempos AS (
                SELECT a.ResponseOID,
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.StatusTime END) AS [Despachado],
                    MAX(CASE WHEN c.Name = 'En Sitio' THEN am.StatusTime END) AS [En Sitio],
                    MAX(CASE WHEN c.Name = 'Terminado' THEN am.StatusTime END) AS [Terminado],
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.Agent END) AS DespachadorOID
                FROM cte_LlamadaEvento a
                INNER JOIN AssignModif am WITH (NOLOCK) ON am.Response = a.ResponseOID
                INNER JOIN Statuses c WITH (NOLOCK) ON c.OID = am.ResourceStatus
                GROUP BY a.ResponseOID
            )
            SELECT
                ROW_NUMBER() OVER (ORDER BY le.[NUMERO_SECUENCIA]) AS [row_num],
                le.[NUMERO_SECUENCIA] AS [Numero de Evento],
                le.[TIPO_RESPUESTA] AS [Tipo de Evento],
                COALESCE(ag_tel.Firstname + ' ' + ag_tel.Lastname, 'Desconocido') AS [Telefonista],
                COALESCE(ag_dsp.Firstname + ' ' + ag_dsp.Lastname, 'Desconocido') AS [Despachador],
                CAST(le.[HORA_LLAMADA_calls] AS TIME(0)) AS [Hora Llamada],
                CAST(le.[FECHA_CREACION] AS TIME(0)) AS [Hora Creacion],
                CAST(tf.[Despachado] AS TIME(0)) AS [Hora Despacho],
                CAST(tf.[En Sitio] AS TIME(0)) AS [Hora En Sitio],
                CAST(tf.[Terminado] AS TIME(0)) AS [Hora Terminado],
                CAST(le.HoraCierre AS TIME(0)) AS [Hora Cierre],
                CONVERT(VARCHAR(8), DATEADD(SECOND,
                    CASE WHEN le.HoraCierre >= le.[FECHA_CREACION]
                    THEN DATEDIFF(SECOND, le.[FECHA_CREACION], le.HoraCierre) ELSE 0 END, 0), 108) AS [Tiempo Total]
            FROM cte_LlamadaEvento le
            LEFT JOIN cte_tiempos tf ON le.ResponseOID = tf.ResponseOID
            LEFT JOIN Agents ag_tel WITH (NOLOCK) ON le.IncidentAgentOID = ag_tel.OID
            LEFT JOIN Agents ag_dsp WITH (NOLOCK) ON tf.DespachadorOID = ag_dsp.OID
            ORDER BY le.[NUMERO_SECUENCIA]
        ";

        $this->resultados = DB::connection('sqlsrv_cad')->select($query);
        $this->busquedaEjecutada = true;
        $this->paginaActual = 1;
    }

    public function getFilteredResults(): array
    {
        if (empty($this->busqueda)) {
            return $this->resultados;
        }

        return array_values(array_filter($this->resultados, function ($row) {
            return str_contains($row->{'Numero de Evento'}, $this->busqueda);
        }));
    }

    public function getPagedResults(): array
    {
        $filtered = $this->getFilteredResults();
        $start = ($this->paginaActual - 1) * $this->porPagina;

        return array_slice($filtered, $start, $this->porPagina);
    }

    public function getTotalPages(): int
    {
        return (int) ceil(count($this->getFilteredResults()) / $this->porPagina);
    }

    public function goToPage(int $page): void
    {
        $this->paginaActual = max(1, min($page, $this->getTotalPages()));
    }

    public function previousPage(): void
    {
        $this->goToPage($this->paginaActual - 1);
    }

    public function nextPage(): void
    {
        $this->goToPage($this->paginaActual + 1);
    }

    public function updatedBusqueda(): void
    {
        $this->paginaActual = 1;
    }
}
