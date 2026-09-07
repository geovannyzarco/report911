<?php

namespace App\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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
        // Clave de cache por evento para evitar recalcular los datos pesados
        // del detalle y las notas cada vez que se abre el modal. Un evento
        // cerrado no cambia, por lo que el TTL de 10 minutos es seguro.
        $cacheClave = 'evento_detalle_'.preg_replace('/[^a-zA-Z0-9_:]/', '', $numeroEvento);

        // Query 1: Datos completos del evento (identificacion, ubicacion, tiempos, personal, resolucion)
        // Usa CTEs para calcular fases de tiempos y mapear la llamada original
        $detalle = Cache::remember($cacheClave, 600, function () use ($numeroEvento) {
            return DB::connection('sqlsrv_cad')->select("
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
                i.SequenceNumber AS [Numero Incidente],
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
        });

        // Query 2: Cronologia de notas del evento (ResponseNotes ordenadas por fecha)
        // Usa UNION para combinar notas del incidente y del despacho especifico
        // Tambien se cachea con el mismo TTL para abrir el modal de forma instantanea
        $notas = Cache::remember($cacheClave.'_notas', 600, function () use ($numeroEvento) {
            return DB::connection('sqlsrv_cad')->select("
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
        });

        // Asigna los resultados a las propiedades del componente para el modal
        $this->detalleEvento = $detalle[0] ?? null;

        // Agrega el numero de incidente formateado (SE911:AAAA:MM:DD:NNNNNN) para mostrar en el modal
        if ($this->detalleEvento) {
            $incSeq = $this->detalleEvento->{'Numero Incidente'} ?? null;
            $creacion = $this->detalleEvento->{'Hora Creacion'} ?? null;
            $numero = null;
            if ($incSeq !== null && $incSeq !== '') {
                $partesInc = explode(':', (string) $incSeq);
                $numero = end($partesInc);
            }
            $fecha = $creacion ? Carbon::parse($creacion) : now();
            $this->detalleEvento->{'Numero Incidente Formateado'} = $numero !== null
                ? "SE911:{$fecha->format('Y')}:{$fecha->format('m')}:{$fecha->format('d')}:{$numero}"
                : ($this->detalleEvento->{'Numero de Evento'} ?? '');
        }

        $this->notasEvento = $notas;

        // Abre el modal usando el evento de Filament (open-modal)
        $this->dispatch('open-modal', id: 'detalle-evento');

        // Notifica al navegador que el mapa debe inicializarse (event Livewire -> JS)
        $this->dispatch('mapa-evento-listo');
    }

    /**
     * Devuelve unicamente la pagina actual de resultados aplicando paginacion real
     * en SQL Server (ROW_NUMBER + filtro de filas) en lugar de traer todas las filas
     * a PHP y hacer array_slice en memoria. Esto reduce drasticamente los datos
     * que viajan por la red y el trabajo de SQL Server por cada pagina.
     */
    #[Computed]
    public function results(): array
    {
        if (! $this->busquedaEjecutada) {
            return [];
        }

        $filas = $this->consultarSql($this->currentPage, $this->perPage);

        return $filas;
    }

    /**
     * Construye y ejecuta la consulta de eventos ya paginada en SQL.
     *
     * Para el modo lista devuelve las filas de la pagina solicitada (ROW_NUMBER entre
     * el rango de la pagina). Para el modo conteo (null page/perPage) devuelve el total
     * de filas usando COUNT(*) sobre el mismo query base, sin transferir datos a PHP.
     *
     * @return array<int, object>|int
     */
    protected function consultarSql(?int $page = null, ?int $perPage = null): object|int|array
    {
        $desde = $this->fechaDesde;
        $hasta = $this->fechaHasta;
        $buscarSoloPorEvento = empty($this->fechaDesde) && empty($this->fechaHasta) && ! empty($this->busqueda);

        // Campo de ordenamiento segun la columna seleccionada
        $orderField = 'le.[NUMERO_SECUENCIA]';
        if ($this->sortColumn === 'tiempo') {
            $orderField = 'CASE WHEN le.HoraCierre >= le.[FECHA_CREACION] THEN DATEDIFF(SECOND, le.[FECHA_CREACION], le.HoraCierre) ELSE 0 END';
        }
        $direction = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // Se usa la consulta por numero de evento (busqueda puntual) o por rango de fechas
        $query = $buscarSoloPorEvento
            ? $this->querySqlPorEvento($orderField, $direction)
            : $this->querySqlPorRango($desde, $hasta, $orderField, $direction);
        $params = $buscarSoloPorEvento ? $this->paramsPorEvento() : [];

        // Ayudante local para construir un statement valido: los CTEs SIEMPRE van al
        // inicio del statement (SQL Server 2008 R2 no permite WITH dentro de FROM()).
        $build = function (string $select) use ($query) {
            return "
                WITH {$query['ctes']}
                $select
            ";
        };

        // Modo conteo: cuenta las filas sin transferir los datos a PHP
        if ($page === null || $perPage === null) {
            $countSql = $build("SELECT COUNT(*) AS total FROM (
                {$query['select']}
            ) AS t");
            $total = DB::connection('sqlsrv_cad')->selectOne($countSql, $params);

            return (int) ($total->total ?? 0);
        }

        // Modo lista: aplica ROW_NUMBER y pagina en SQL Server
        $inicio = (($page - 1) * $perPage) + 1;
        $fin = $page * $perPage;

        $paginatedSql = $build("SELECT * FROM (
            {$query['select']}
        ) AS paginated WHERE rn BETWEEN $inicio AND $fin");
        $filas = DB::connection('sqlsrv_cad')->select($paginatedSql, $params);

        // Si ademas se filtro por texto dentro del rango, se mantiene el filtro en PHP
        if (! $buscarSoloPorEvento && ! empty($this->busqueda)) {
            $filas = array_values(array_filter($filas, function ($row) {
                return str_contains($row->{'Numero de Evento'}, $this->busqueda);
            }));
        }

        return $this->formatearNumerosIncidente($filas);
    }

    /**
     * Construye la consulta CTE para la busqueda puntual por numero de evento.
     * Retorna un arreglo con las definiciones de los CTEs ('ctes') y el SELECT
     * principal paginable ('select'), para que el WITH quede siempre al inicio
     * del statement (SQL Server 2008 R2 no admite WITH dentro de un FROM()).
     *
     * @return array{ctes: string, select: string}
     */
    protected function querySqlPorEvento(string $orderField, string $direction): array
    {
        $fechaSql = $this->fechaSqlDeBusqueda();
        $prefijoResponses = $this->prefijoResponses();

        // Se evita el uso de OR entre dos LIKE distintos: SQL Server 2008 R2 genera un
        // plan ineficiente (escaneo completo de 6.17M filas) que excede los 60s de PHP.
        // En su lugar se separan las dos busquedas en dos subconsultas unidas con UNION ALL:
        //  - Rama A: Responses.SequenceNumber por prefijo SE911:AAAA:MM:DD:% (rapida)
        //  - Rama B: Incidents.SequenceNumber por sufijo %:NNNN + filtro de fecha (rapida)
        $ramaA = '
            SELECT a.Incident, a.ResponseType, a.SequenceNumber AS [NUMERO_SECUENCIA],
                a.OID AS ResponseOID, g.Name AS [TIPO_RESPUESTA],
                a.CreationTime AS [FECHA_CREACION], i.Agent AS IncidentAgentOID,
                i.SequenceNumber AS [NUMERO_INCIDENTE_FULL],
                CASE WHEN a.Status = 7 THEN a.StatusTime ELSE NULL END AS HoraCierre,
                MIN(c.CreationTime) AS [HORA_LLAMADA_calls]
            FROM Responses AS a WITH (NOLOCK)
            INNER JOIN Incidents AS i WITH (NOLOCK) ON a.Incident = i.OID
            INNER JOIN ResponseTypes AS g WITH (NOLOCK) ON g.OID = a.ResponseType
            LEFT JOIN Calls c WITH (NOLOCK) ON c.Incident = a.Incident
            WHERE a.SequenceNumber LIKE ?
            GROUP BY a.Incident, a.ResponseType, a.SequenceNumber, a.OID, g.Name, a.CreationTime, i.Agent, i.SequenceNumber, a.Status, a.StatusTime
        ';
        $ramaB = "
            SELECT a.Incident, a.ResponseType, a.SequenceNumber AS [NUMERO_SECUENCIA],
                a.OID AS ResponseOID, g.Name AS [TIPO_RESPUESTA],
                a.CreationTime AS [FECHA_CREACION], i.Agent AS IncidentAgentOID,
                i.SequenceNumber AS [NUMERO_INCIDENTE_FULL],
                CASE WHEN a.Status = 7 THEN a.StatusTime ELSE NULL END AS HoraCierre,
                MIN(c.CreationTime) AS [HORA_LLAMADA_calls]
            FROM Incidents AS i WITH (NOLOCK)
            INNER JOIN Responses AS a WITH (NOLOCK) ON a.Incident = i.OID
            INNER JOIN ResponseTypes AS g WITH (NOLOCK) ON g.OID = a.ResponseType
            LEFT JOIN Calls c WITH (NOLOCK) ON c.Incident = a.Incident
            WHERE i.SequenceNumber LIKE ?
                AND (i.CreationTime >= '{$fechaSql}' AND i.CreationTime < DATEADD(DAY, 1, '{$fechaSql}'))
            GROUP BY a.Incident, a.ResponseType, a.SequenceNumber, a.OID, g.Name, a.CreationTime, i.Agent, i.SequenceNumber, a.Status, a.StatusTime
        ";

        // Cuando la busqueda no trae fecha (solo numero), no se puede usar el prefijo de
        // Responses; se deja unicamente la rama del incidente (respaldo) sin el prefijo.
        $unionLlamadaEvento = $prefijoResponses !== ''
            ? "$ramaA UNION ALL $ramaB"
            : 'SELECT a.Incident, a.ResponseType, a.SequenceNumber AS [NUMERO_SECUENCIA],
                a.OID AS ResponseOID, g.Name AS [TIPO_RESPUESTA],
                a.CreationTime AS [FECHA_CREACION], i.Agent AS IncidentAgentOID,
                i.SequenceNumber AS [NUMERO_INCIDENTE_FULL],
                CASE WHEN a.Status = 7 THEN a.StatusTime ELSE NULL END AS HoraCierre,
                MIN(c.CreationTime) AS [HORA_LLAMADA_calls]
            FROM Responses AS a WITH (NOLOCK)
            INNER JOIN Incidents AS i WITH (NOLOCK) ON a.Incident = i.OID
            INNER JOIN ResponseTypes AS g WITH (NOLOCK) ON g.OID = a.ResponseType
            LEFT JOIN Calls c WITH (NOLOCK) ON c.Incident = a.Incident
            WHERE a.SequenceNumber LIKE ? OR i.SequenceNumber LIKE ?
            GROUP BY a.Incident, a.ResponseType, a.SequenceNumber, a.OID, g.Name, a.CreationTime, i.Agent, i.SequenceNumber, a.Status, a.StatusTime';

        return [
            'ctes' => "
            cte_LlamadaEvento AS (
                $unionLlamadaEvento
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
            )",
            'select' => "
            SELECT
                le.[NUMERO_SECUENCIA] AS [Numero de Evento],
                le.[NUMERO_INCIDENTE_FULL] AS [Numero Incidente],
                le.[TIPO_RESPUESTA] AS [Tipo de Evento],
                COALESCE(ag_tel.Firstname + ' ' + ag_tel.Lastname, 'Desconocido') AS [Telefonista],
                COALESCE(ag_dsp.Firstname + ' ' + ag_dsp.Lastname, 'Desconocido') AS [Despachador],
                CAST(le.[HORA_LLAMADA_calls] AS TIME(0)) AS [Hora Llamada],
                CAST(le.[FECHA_CREACION] AS TIME(0)) AS [Hora Creacion],
                le.[FECHA_CREACION] AS [FECHA_CREACION_RAW],
                CAST(tf.[Despachado] AS TIME(0)) AS [Hora Despacho],
                CAST(tf.[En Sitio] AS TIME(0)) AS [Hora En Sitio],
                CAST(tf.[Terminado] AS TIME(0)) AS [Hora Terminado],
                CAST(le.HoraCierre AS TIME(0)) AS [Hora Cierre],
                CONVERT(VARCHAR(8), DATEADD(SECOND,
                    CASE WHEN le.HoraCierre >= le.[FECHA_CREACION]
                    THEN DATEDIFF(SECOND, le.[FECHA_CREACION], le.HoraCierre) ELSE 0 END, 0), 108) AS [Tiempo Total],
                ROW_NUMBER() OVER (ORDER BY $orderField $direction) AS rn
            FROM cte_LlamadaEvento le
            LEFT JOIN cte_tiempos tf ON le.ResponseOID = tf.ResponseOID
            LEFT JOIN Agents ag_tel WITH (NOLOCK) ON le.IncidentAgentOID = ag_tel.OID
            LEFT JOIN Agents ag_dsp WITH (NOLOCK) ON tf.DespachadorOID = ag_dsp.OID
            ",
        ];
    }

    /**
     * Construye la consulta CTE para la busqueda por rango de fechas.
     * Retorna un arreglo con las definiciones de los CTEs ('ctes') y el SELECT
     * principal paginable ('select'), manteniendo el WITH al inicio del statement.
     *
     * @return array{ctes: string, select: string}
     */
    protected function querySqlPorRango(string $desde, string $hasta, string $orderField, string $direction): array
    {
        return [
            'ctes' => "
            cte_Calls AS (
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
                    i.SequenceNumber AS [NUMERO_INCIDENTE_FULL],
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
            )",
            'select' => "
            SELECT
                le.[NUMERO_SECUENCIA] AS [Numero de Evento],
                le.[NUMERO_INCIDENTE_FULL] AS [Numero Incidente],
                le.[TIPO_RESPUESTA] AS [Tipo de Evento],
                COALESCE(ag_tel.Firstname + ' ' + ag_tel.Lastname, 'Desconocido') AS [Telefonista],
                COALESCE(ag_dsp.Firstname + ' ' + ag_dsp.Lastname, 'Desconocido') AS [Despachador],
                CAST(le.[HORA_LLAMADA_calls] AS TIME(0)) AS [Hora Llamada],
                CAST(le.[FECHA_CREACION] AS TIME(0)) AS [Hora Creacion],
                le.[FECHA_CREACION] AS [FECHA_CREACION_RAW],
                CAST(tf.[Despachado] AS TIME(0)) AS [Hora Despacho],
                CAST(tf.[En Sitio] AS TIME(0)) AS [Hora En Sitio],
                CAST(tf.[Terminado] AS TIME(0)) AS [Hora Terminado],
                CAST(le.HoraCierre AS TIME(0)) AS [Hora Cierre],
                CONVERT(VARCHAR(8), DATEADD(SECOND,
                    CASE WHEN le.HoraCierre >= le.[FECHA_CREACION]
                    THEN DATEDIFF(SECOND, le.[FECHA_CREACION], le.HoraCierre) ELSE 0 END, 0), 108) AS [Tiempo Total],
                ROW_NUMBER() OVER (ORDER BY $orderField $direction) AS rn
            FROM cte_LlamadaEvento le
            LEFT JOIN cte_tiempos tf ON le.ResponseOID = tf.ResponseOID
            LEFT JOIN Agents ag_tel WITH (NOLOCK) ON le.IncidentAgentOID = ag_tel.OID
            LEFT JOIN Agents ag_dsp WITH (NOLOCK) ON tf.DespachadorOID = ag_dsp.OID
            ",
        ];
    }

    /**
     * Extrae la parte YYYYMMDD (sin separadores) de la busqueda SE911:AAAA:AA:AA:NNNN
     * para usarla como literal SQL en la comparacion de fechas del incidente.
     */
    protected function fechaSqlDeBusqueda(): string
    {
        $partes = explode(':', $this->busqueda);
        if (count($partes) >= 4) {
            return $partes[1].$partes[2].$partes[3];
        }

        return '';
    }

    /**
     * Retorna los parametros con los que se bindean las consultas de busqueda por evento.
     * Si la busqueda trae fecha (SE911:AAAA:MM:DD:NNNN), el primer parametro es el prefijo
     * indexable para Responses.SequenceNumber; de lo contrario se usa el LIKE amplio.
     */
    protected function paramsPorEvento(): array
    {
        $partes = explode(':', $this->busqueda);
        $ultimaParte = trim((string) end($partes));
        $prefijoResponses = $this->prefijoResponses();

        if ($prefijoResponses !== '') {
            return [
                $prefijoResponses,
                filled($ultimaParte) ? "%:{$ultimaParte}" : '%:%',
            ];
        }

        return [
            "%{$this->busqueda}%",
            filled($ultimaParte) ? "%:{$ultimaParte}" : '%:%',
        ];
    }

    /**
     * Construye el prefijo de busqueda para Responses.SequenceNumber usando el termino
     * completo (ej: 'SE911:2026:09:04:288607%'). Es indexable (comienza sin wildcard) y
     * preciso: solo matchea la secuencia diaria exacta, en lugar de devolver todos los
     * eventos del dia. Devuelve '' si la busqueda no pareciera un numero SE911.
     */
    protected function prefijoResponses(): string
    {
        if (str_starts_with($this->busqueda, 'SE911:')) {
            return $this->busqueda.'%';
        }

        return '';
    }

    /**
     * Convierte el SequenceNumber compuesto del Incident (ej: 02:03:287649) al formato
     * SE911:AAAA:MM:DD:NNNNNN usado por el widget "Incidentes Activos sin Cerrar".
     * Se anade la propiedad "Numero Incidente Formateado" a cada fila.
     *
     * @param  array  $rows  Resultados crudos de la consulta
     * @return array Filas con la propiedad formateada
     */
    protected function formatearNumerosIncidente(array $rows): array
    {
        foreach ($rows as $row) {
            $incidentSeq = $row->{'Numero Incidente'} ?? null;

            // Determina la fecha a usar: se toma de FECHA_CREACION_RAW (datetime completo del evento).
            $fecha = null;
            if (isset($row->{'FECHA_CREACION_RAW'}) && str_contains((string) $row->{'FECHA_CREACION_RAW'}, '-')) {
                $fecha = Carbon::parse($row->{'FECHA_CREACION_RAW'});
            }

            $numero = null;
            if ($incidentSeq !== null && $incidentSeq !== '') {
                $partes = explode(':', (string) $incidentSeq);
                $numero = end($partes);
            }

            if ($numero !== null) {
                $anio = $fecha ? $fecha->format('Y') : date('Y');
                $mes = $fecha ? $fecha->format('m') : date('m');
                $dia = $fecha ? $fecha->format('d') : date('d');
                $row->{'Numero Incidente Formateado'} = "SE911:{$anio}:{$mes}:{$dia}:{$numero}";
            } else {
                $row->{'Numero Incidente Formateado'} = $row->{'Numero de Evento'};
            }
        }

        return $rows;
    }

    #[Computed]
    public function pagedResults(): array
    {
        // Los resultados ya vienen paginados desde SQL Server (solo la pagina actual)
        return $this->results();
    }

    #[Computed]
    public function total(): int
    {
        // Consulta el total mediante COUNT(*) en SQL Server, sin transferir todas las filas
        return (int) $this->consultarSql(null, null);
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
