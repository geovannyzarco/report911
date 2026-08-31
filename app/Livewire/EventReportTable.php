<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Livewire Component: EventReportTable
 * Tabla de eventos del CAD con paginacion manual y modal de detalles.
 */
class EventReportTable extends Component
{
    /** @var string Fecha de inicio del filtro de busqueda */
    public string $fechaDesde = '';

    /** @var string Fecha de fin del filtro de busqueda */
    public string $fechaHasta = '';

    /** @var string Texto de busqueda por numero de evento */
    public string $busqueda = '';

    /** @var int Cantidad de registros por pagina */
    public int $perPage = 25;

    /** @var int Pagina actual */
    public int $currentPage = 1;

    /** @var bool Indica si ya se ejecuto una busqueda */
    public bool $busquedaEjecutada = false;

    /** @var string Columna actual de ordenamiento */
    public string $sortColumn = 'evento';

    /** @var string Direccion del ordenamiento (asc/desc) */
    public string $sortDirection = 'asc';

    /** @var object|null Objeto con todos los datos del evento seleccionado para el modal */
    public ?object $detalleEvento = null;

    /** @var array Lista de notas cronologicas del evento seleccionado */
    public array $notasEvento = [];

    #[On('search')]
    public function search(string $desde, string $hasta, string $busqueda = ''): void
    {
        $this->fechaDesde = $desde;
        $this->fechaHasta = $hasta;
        $this->busqueda = $busqueda;
        $this->busquedaEjecutada = true;
        $this->currentPage = 1;
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            // Para el tiempo, por defecto ordenamos descendente (mayor a menor)
            $this->sortDirection = $column === 'tiempo' ? 'desc' : 'asc';
        }

        $this->currentPage = 1;
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = max(1, min($page, $this->totalPages()));
    }

    public function previousPage(): void
    {
        $this->currentPage = max(1, $this->currentPage - 1);
    }

    public function nextPage(): void
    {
        $this->currentPage = min($this->totalPages(), $this->currentPage + 1);
    }

    /** Resetea a pagina 1 cuando cambia la cantidad de registros por pagina */
    public function updatingPerPage(): void
    {
        $this->currentPage = 1;
    }

    /**
     * Abre el modal de detalles con la informacion completa del evento.
     * Ejecuta dos queries SQL Server: uno para datos del evento y otro para las notas.
     *
     * @param  string  $numeroEvento  Numero de secuencia del evento (ej: SE911:2026:07:01:0067)
     */
    public function verDetalle(string $numeroEvento): void
    {
        // Query 1: Datos completos del evento (identificacion, ubicacion, tiempos, personal, resolucion)
        // Usa CTEs para calcular fases de tiempos y mapear la llamada original
        $detalle = DB::connection('sqlsrv_cad')->select("
            WITH cte_tiempos_fases AS (
                SELECT
                    am.Response,
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.StatusTime END) AS [Despachado],
                    MAX(CASE WHEN c.Name = 'En Ruta' THEN am.StatusTime END) AS [En Ruta],
                    MAX(CASE WHEN c.Name = 'En Sitio' THEN am.StatusTime END) AS [En Sitio],
                    MAX(CASE WHEN c.Name = 'Terminado' THEN am.StatusTime END) AS [Terminado],
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.Agent END) AS DespachadorAgentOID,
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.Workstation END) AS DespachadorWorkstationOID
                FROM AssignModif am WITH (NOLOCK)
                INNER JOIN Statuses c WITH (NOLOCK) ON c.OID = am.ResourceStatus
                WHERE am.Response IN (SELECT OID FROM Responses WITH (NOLOCK) WHERE SequenceNumber = ?)
                GROUP BY am.Response
            ),
            cte_Calls AS (
                SELECT
                    c.Incident,
                    MIN(c.CreationTime) AS [HoraLlamada],
                    MIN(c.Caller) AS CallerOID,
                    MIN(c.Origin) AS OriginOID,
                    MAX(c.CustomerName) AS CustomerName
                FROM Calls c WITH (NOLOCK)
                WHERE c.Incident IN (SELECT Incident FROM Responses WITH (NOLOCK) WHERE SequenceNumber = ?)
                GROUP BY c.Incident
            )
            SELECT
                r.SequenceNumber AS [Numero de Evento],
                rt.Name AS [Tipo de Evento],
                pri.Name AS [Prioridad],
                ag.Name AS [Agencia],
                st.Name AS [Estado Actual],
                ori.Name AS [Origen de Entrada],
                adr.CommonPlace AS [Lugar Comun],
                adr.FreeFormatAddress AS [Direccion Completa],
                str_main.Name AS [Calle Principal],
                str_cross1.Name AS [Cruce Calle 1],
                str_cross2.Name AS [Cruce Calle 2],
                adr.XCoordinate AS [Coordenada X],
                adr.YCoordinate AS [Coordenada Y],
                z.Name AS [Zona],
                COALESCE(clr.PhoneOwnerName, c_time.CustomerName, 'No Registrado') AS [Nombre Informante],
                COALESCE(clr.PhoneNumber, r.PowerPhoneNumber, 'Sin Telefono') AS [Telefono Informante],
                clt.Name AS [Tipo Informante],
                COALESCE(ag_tel.Firstname + ' ' + ag_tel.Lastname, ag_tel.DisplayName, 'Desconocido') AS [Telefonista],
                ag_tel.LogonName AS [Usuario Telefonista],
                w_tel.WorkstationNumber AS [Puesto Telefonista],
                COALESCE(ag_dsp.Firstname + ' ' + ag_dsp.Lastname, ag_dsp.DisplayName, 'Desconocido') AS [Despachador],
                ag_dsp.LogonName AS [Usuario Despachador],
                w_dsp.WorkstationNumber AS [Puesto Despachador],
                c_time.[HoraLlamada] AS [Hora Llamada],
                r.CreationTime AS [Hora Creacion],
                tf.[Despachado] AS [Hora Despachado],
                tf.[En Ruta] AS [Hora En Ruta],
                tf.[En Sitio] AS [Hora En Sitio],
                tf.[Terminado] AS [Hora Terminado],
                CASE WHEN r.Status = 7 THEN r.StatusTime ELSE NULL END AS [Hora Cierre],
                CONVERT(VARCHAR(8), DATEADD(SECOND,
                    CASE WHEN tf.[Despachado] >= r.CreationTime THEN DATEDIFF(SECOND, r.CreationTime, tf.[Despachado]) ELSE 0 END, 0), 108) AS [Duracion Despacho],
                CONVERT(VARCHAR(8), DATEADD(SECOND,
                    CASE WHEN tf.[En Sitio] >= tf.[Despachado] THEN DATEDIFF(SECOND, tf.[Despachado], tf.[En Sitio]) ELSE 0 END, 0), 108) AS [Tiempo Viaje],
                CONVERT(VARCHAR(8), DATEADD(SECOND,
                    CASE WHEN r.Status = 7 AND r.StatusTime >= r.CreationTime THEN DATEDIFF(SECOND, r.CreationTime, r.StatusTime) ELSE 0 END, 0), 108) AS [Duracion Evento],
                disp.Name AS [Codigo Cierre]
            FROM Responses r WITH (NOLOCK)
            INNER JOIN Incidents i WITH (NOLOCK) ON r.Incident = i.OID
            INNER JOIN ResponseTypes rt WITH (NOLOCK) ON r.ResponseType = rt.OID
            LEFT JOIN Priorities pri WITH (NOLOCK) ON r.Priority = pri.OID
            LEFT JOIN Agencies ag WITH (NOLOCK) ON r.Agency = ag.OID
            LEFT JOIN Statuses st WITH (NOLOCK) ON r.Status = st.OID
            LEFT JOIN cte_Calls c_time ON r.Incident = c_time.Incident
            LEFT JOIN Origins ori WITH (NOLOCK) ON c_time.OriginOID = ori.OID
            LEFT JOIN Callers clr WITH (NOLOCK) ON c_time.CallerOID = clr.OID
            LEFT JOIN CallerTypes clt WITH (NOLOCK) ON clr.CallerType = clt.OID
            LEFT JOIN Addresses adr WITH (NOLOCK) ON r.Address = adr.OID
            LEFT JOIN Streets str_main WITH (NOLOCK) ON adr.Street = str_main.OID
            LEFT JOIN Streets str_cross1 WITH (NOLOCK) ON adr.CrossStreet1 = str_cross1.OID
            LEFT JOIN Streets str_cross2 WITH (NOLOCK) ON adr.CrossStreet2 = str_cross2.OID
            LEFT JOIN Zones z WITH (NOLOCK) ON r.Zone = z.OID
            LEFT JOIN cte_tiempos_fases tf ON r.OID = tf.Response
            LEFT JOIN Agents ag_tel WITH (NOLOCK) ON i.Agent = ag_tel.OID
            LEFT JOIN WorkStations w_tel WITH (NOLOCK) ON i.WorkStation = w_tel.OID
            LEFT JOIN Agents ag_dsp WITH (NOLOCK) ON tf.DespachadorAgentOID = ag_dsp.OID
            LEFT JOIN WorkStations w_dsp WITH (NOLOCK) ON tf.DespachadorWorkstationOID = w_dsp.OID
            LEFT JOIN FinalizedResponsesDispCodes fr WITH (NOLOCK) ON r.OID = fr.Response AND (fr.Deleted = 0 OR fr.Deleted IS NULL)
            LEFT JOIN DispositionCodes disp WITH (NOLOCK) ON fr.DispositionCode = disp.OID
            WHERE r.SequenceNumber = ?
        ", [$numeroEvento, $numeroEvento, $numeroEvento]);

        // Query 2: Cronologia de notas del evento (ResponseNotes ordenadas por fecha)
        // Usa UNION para combinar notas del incidente y del despacho especifico
        $notas = DB::connection('sqlsrv_cad')->select("
            WITH cte_EventOIDs AS (
                SELECT OID AS ResponseOID, Incident AS IncidentOID
                FROM Responses WITH (NOLOCK)
                WHERE SequenceNumber = ?

                UNION ALL

                SELECT NULL AS ResponseOID, OID AS IncidentOID
                FROM Incidents WITH (NOLOCK)
                WHERE SequenceNumber = ?
            )
            SELECT
                rn.[TimeStamp1] AS [Fecha y Hora],
                COALESCE(ag.DisplayName, ag.LogonName, ag.Firstname + ' ' + ag.Lastname, 'Sistema/Auto') AS [Operador],
                w.WorkstationNumber AS [Estacion],
                rn.[Notes] AS [Nota]
            FROM (
                SELECT TimeStamp1, Agent, WorkStation, Notes
                FROM ResponseNotes WITH (NOLOCK)
                WHERE Incident IN (SELECT IncidentOID FROM cte_EventOIDs)

                UNION

                SELECT TimeStamp1, Agent, WorkStation, Notes
                FROM ResponseNotes WITH (NOLOCK)
                WHERE Response IN (SELECT ResponseOID FROM cte_EventOIDs WHERE ResponseOID IS NOT NULL)
            ) rn
            LEFT JOIN Agents ag WITH (NOLOCK) ON rn.Agent = ag.OID
            LEFT JOIN WorkStations w WITH (NOLOCK) ON rn.WorkStation = w.OID
            ORDER BY rn.TimeStamp1 ASC
        ", [$numeroEvento, $numeroEvento]);

        // Asigna los resultados a las propiedades del componente para el modal
        $this->detalleEvento = $detalle[0] ?? null;
        $this->notasEvento = $notas;

        // Abre el modal usando el evento de Filament (open-modal)
        $this->dispatch('open-modal', id: 'detalle-evento');

        // Notifica al navegador que el mapa debe inicializarse (event Livewire -> JS)
        $this->dispatch('mapa-evento-listo');
    }

    #[Computed]
    public function results(): array
    {
        if (! $this->busquedaEjecutada) {
            return [];
        }

        $desde = $this->fechaDesde;
        $hasta = $this->fechaHasta;

        $orderField = 'le.[NUMERO_SECUENCIA]';
        if ($this->sortColumn === 'tiempo') {
            $orderField = 'CASE WHEN le.HoraCierre >= le.[FECHA_CREACION] THEN DATEDIFF(SECOND, le.[FECHA_CREACION], le.HoraCierre) ELSE 0 END';
        }
        $direction = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';

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
            ORDER BY $orderField $direction
        ";

        $allResults = DB::connection('sqlsrv_cad')->select($query);

        if (! empty($this->busqueda)) {
            $allResults = array_filter($allResults, function ($row) {
                return str_contains($row->{'Numero de Evento'}, $this->busqueda);
            });
        }

        return array_values($allResults);
    }

    #[Computed]
    public function pagedResults(): array
    {
        $all = $this->results();
        $start = ($this->currentPage - 1) * $this->perPage;

        return array_slice($all, $start, $this->perPage);
    }

    #[Computed]
    public function total(): int
    {
        return count($this->results());
    }

    #[Computed]
    public function totalPages(): int
    {
        return max(1, (int) ceil($this->total() / $this->perPage));
    }

    public function render()
    {
        return view('livewire.event-report-table');
    }
}
