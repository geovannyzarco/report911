<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Livewire Component: EventReportTable
 * Tabla de eventos del CAD con paginacion manual.
 */
class EventReportTable extends Component
{
    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public string $busqueda = '';

    public int $perPage = 25;

    public int $currentPage = 1;

    public bool $busquedaEjecutada = false;

    protected $listeners = ['search' => 'search'];

    public function search(string $desde, string $hasta): void
    {
        $this->fechaDesde = $desde;
        $this->fechaHasta = $hasta;
        $this->busquedaEjecutada = true;
        $this->currentPage = 1;
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = max(1, $page);
    }

    public function previousPage(): void
    {
        $this->currentPage = max(1, $this->currentPage - 1);
    }

    public function nextPage(): void
    {
        $this->currentPage++;
    }

    public function updatingPerPage(): void
    {
        $this->currentPage = 1;
    }

    public function getResultsProperty(): array
    {
        if (! $this->busquedaEjecutada) {
            return [];
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

        return array_values($allResults);
    }

    public function getPagedResultsProperty(): array
    {
        $all = $this->results;
        $start = ($this->currentPage - 1) * $this->perPage;

        return array_slice($all, $start, $this->perPage);
    }

    public function getTotalProperty(): int
    {
        return count($this->results);
    }

    public function getTotalPagesProperty(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    public function render()
    {
        return view('livewire.event-report-table');
    }
}
