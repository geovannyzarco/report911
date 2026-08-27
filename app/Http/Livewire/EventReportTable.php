<?php

namespace App\Http\Livewire;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire Component: EventReportTable
 * Tabla de eventos del CAD con paginacion nativa de Livewire.
 */
class EventReportTable extends Component
{
    use WithPagination;

    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public string $busqueda = '';

    public int $perPage = 25;

    public bool $busquedaEjecutada = false;

    protected $listeners = ['search' => 'search'];

    public function search(string $desde, string $hasta): void
    {
        $this->fechaDesde = $desde;
        $this->fechaHasta = $hasta;
        $this->busquedaEjecutada = true;
        $this->resetPage();
    }

    public function getResultsProperty(): LengthAwarePaginator
    {
        if (! $this->busquedaEjecutada) {
            return new LengthAwarePaginator([], 0, $this->perPage);
        }

        $desde = $this->fechaDesde;
        $hasta = $this->fechaHasta;

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

        $allResults = DB::connection('sqlsrv_cad')->select($query);

        if (! empty($this->busqueda)) {
            $allResults = array_filter($allResults, function ($row) {
                return str_contains($row->{'Numero de Evento'}, $this->busqueda);
            });
        }

        $allResults = array_values($allResults);

        $total = count($allResults);
        $start = ($this->page - 1) * $this->perPage;
        $paginatedResults = array_slice($allResults, $start, $this->perPage);

        return new LengthAwarePaginator(
            $paginatedResults,
            $total,
            $this->perPage,
            $this->page,
            ['path' => request()->url()]
        );
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.event-report-table');
    }
}
