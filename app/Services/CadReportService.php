<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de reportes para la base de datos CAD ViperCAD_Log.
 * Encapsula todas las queries optimizadas de SQL Server.
 *
 * NOTA: Las fechas se insertan como literales SQL porque el driver PDO
 * de SQL Server no bindea correctamente los parametros datetime con
 * configuracion de idioma en espanol. Carbon provee input seguro.
 */
class CadReportService
{
    private const OVERFLOW_VALUE = 2147483647;

    /**
     * Formato YYYYMMDD para SQL Server.
     */
    private function sqlDate(Carbon $date): string
    {
        return $date->format('Ymd');
    }

    /**
     * Reporte de tiempos de respuesta promedio por agencia, prioridad y tipo de incidente.
     */
    public function getTiemposRespuestaPromedio(
        Carbon $desde,
        Carbon $hasta,
        ?string $agencia = null,
        ?string $prioridad = null,
    ): Collection {
        $desdeStr = $this->sqlDate($desde);
        $hastaStr = $this->sqlDate($hasta->copy()->addDay());

        $query = DB::connection('sqlsrv_cad')->table('VMIS_RESP_STATUSCHANGES as sc')
            ->select([
                'ag.Name as Agencia',
                'pr.Name as Prioridad',
                'rt.Name as TipoIncidente',
                DB::raw('COUNT(DISTINCT r.OID) as TotalDespachos'),
                DB::raw('ROUND(AVG(CASE WHEN sc.STATUS = 1 AND sc.NEXTSTATUS = 2 AND sc.ELAPSEDTIME_MS != '.self::OVERFLOW_VALUE.' THEN sc.ELAPSEDTIME_MS / 1000.0 ELSE NULL END), 2) as PromedioDespacho_Seg'),
                DB::raw('ROUND(AVG(CASE WHEN sc.STATUS = 2 AND sc.NEXTSTATUS = 3 AND sc.ELAPSEDTIME_MS != '.self::OVERFLOW_VALUE.' THEN sc.ELAPSEDTIME_MS / 1000.0 ELSE NULL END), 2) as PromedioEnRuta_Seg'),
                DB::raw('ROUND(AVG(CASE WHEN sc.STATUS = 3 AND sc.NEXTSTATUS = 4 AND sc.ELAPSEDTIME_MS != '.self::OVERFLOW_VALUE.' THEN sc.ELAPSEDTIME_MS / 1000.0 ELSE NULL END), 2) as PromedioTransito_Seg'),
                DB::raw('ROUND(AVG(CASE WHEN sc.STATUS = 4 AND sc.NEXTSTATUS = 6 AND sc.ELAPSEDTIME_MS != '.self::OVERFLOW_VALUE.' THEN sc.ELAPSEDTIME_MS / 1000.0 ELSE NULL END), 2) as PromedioEnSitio_Seg'),
            ])
            ->join('Responses as r', 'sc.RESPONSE', '=', 'r.OID')
            ->leftJoin('Agencies as ag', 'r.Agency', '=', 'ag.OID')
            ->leftJoin('Priorities as pr', 'r.Priority', '=', 'pr.OID')
            ->leftJoin('ResponseTypes as rt', 'r.ResponseType', '=', 'rt.OID')
            ->whereRaw("sc.STATUSTIME >= '$desdeStr'")
            ->whereRaw("sc.STATUSTIME <= '$hastaStr'")
            ->groupBy('ag.Name', 'pr.Name', 'rt.Name');

        if ($agencia) {
            $query->where('ag.Name', $agencia);
        }

        if ($prioridad) {
            $query->where('pr.Name', $prioridad);
        }

        return $query->orderBy('Agencia')
            ->orderByDesc('PromedioDespacho_Seg')
            ->get();
    }

    /**
     * Reporte de volumen de incidentes por clasificacion y prioridad.
     */
    public function getVolumenIncidentes(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
        $desdeStr = $this->sqlDate($desde);
        $hastaStr = $this->sqlDate($hasta->copy()->addDay());

        return DB::connection('sqlsrv_cad')->table('Incidents as i')
            ->select([
                DB::raw('CAST(i.CreationTime AS DATE) as Fecha'),
                'cl.Name as ClasificacionIncidente',
                'pr.Name as Prioridad',
                DB::raw('COUNT(i.OID) as CantidadIncidentes'),
                DB::raw('SUM(CASE WHEN i.Finalized = 1 THEN 1 ELSE 0 END) as Finalizados'),
                DB::raw('SUM(CASE WHEN i.Deleted = 1 THEN 1 ELSE 0 END) as Eliminados'),
            ])
            ->leftJoin('Classifications as cl', 'i.Classification', '=', 'cl.OID')
            ->leftJoin('Priorities as pr', 'i.Priority', '=', 'pr.OID')
            ->whereRaw("i.CreationTime >= '$desdeStr'")
            ->whereRaw("i.CreationTime <= '$hastaStr'")
            ->where(function ($q) {
                $q->where('i.Deleted', 0)
                    ->orWhereNull('i.Deleted');
            })
            ->groupBy(DB::raw('CAST(i.CreationTime AS DATE)'), 'cl.Name', 'pr.Name')
            ->orderByDesc('Fecha')
            ->orderByDesc('CantidadIncidentes')
            ->get();
    }

    /**
     * Reporte de utilizacion y eficiencia de unidades (recursos).
     */
    public function getUtilizacionUnidades(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
        $desdeStr = $this->sqlDate($desde);
        $hastaStr = $this->sqlDate($hasta->copy()->addDay());

        return DB::connection('sqlsrv_cad')->table('VMIS_RESP_RESOACTIVETIMES as rat')
            ->select([
                'res.Name as CodigoUnidad',
                'sta.Name as EstacionBase',
                DB::raw('COUNT(rat.RESPONSE) as TotalDespachos'),
                DB::raw('ROUND(SUM(DATEDIFF(second, rat.UTCTIME_START, rat.UTCTIME_END)) / 3600.0, 2) as HorasServicioActivo'),
                DB::raw('ROUND(AVG(DATEDIFF(second, rat.UTCTIME_START, rat.UTCTIME_END)) / 60.0, 2) as PromedioMinutosPorDespacho'),
            ])
            ->join('Resources as res', 'rat.RESOURCE', '=', 'res.OID')
            ->leftJoin('Stations as sta', 'res.Station', '=', 'sta.OID')
            ->whereRaw("rat.UTCTIME_START >= '$desdeStr'")
            ->whereRaw("rat.UTCTIME_START <= '$hastaStr'")
            ->groupBy('res.Name', 'sta.Name')
            ->orderByDesc('HorasServicioActivo')
            ->get();
    }

    /**
     * Reporte de codigos de cierre / disposiciones de emergencias.
     */
    public function getDisposicionesCierre(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
        $desdeStr = $this->sqlDate($desde);
        $hastaStr = $this->sqlDate($hasta->copy()->addDay());

        return DB::connection('sqlsrv_cad')->table('Responses as r')
            ->select([
                'ag.Name as Agencia',
                'rt.Name as TipoRespuesta',
                'dc.Name as CodigoCierre',
                DB::raw('COUNT(r.OID) as Cantidad'),
            ])
            ->leftJoin('Agencies as ag', 'r.Agency', '=', 'ag.OID')
            ->leftJoin('ResponseTypes as rt', 'r.ResponseType', '=', 'rt.OID')
            ->join('FinalizedResponsesDispCodes as frd', function ($join) {
                $join->on('r.OID', '=', 'frd.Response')
                    ->where(function ($q) {
                        $q->where('frd.Deleted', 0)
                            ->orWhereNull('frd.Deleted');
                    });
            })
            ->join('DispositionCodes as dc', 'frd.DispositionCode', '=', 'dc.OID')
            ->whereRaw("r.CreationTime >= '$desdeStr'")
            ->whereRaw("r.CreationTime <= '$hastaStr'")
            ->groupBy('ag.Name', 'rt.Name', 'dc.Name')
            ->orderBy('Agencia')
            ->orderByDesc('Cantidad')
            ->get();
    }

    /**
     * Reporte de desempeno y carga de trabajo de telefonistas/operadores.
     */
    public function getDesempenoOperadores(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
        $desdeStr = $this->sqlDate($desde);
        $hastaStr = $this->sqlDate($hasta->copy()->addDay());

        return DB::connection('sqlsrv_cad')->table('Calls as c')
            ->select([
                DB::raw('COALESCE(a.DisplayName, a.LogonName) as NombreOperador'),
                'a.BadgeNumber as Placa',
                DB::raw('COUNT(c.OID) as LlamadasAtendidas'),
                DB::raw('SUM(CASE WHEN c.CallState = 20 THEN 1 ELSE 0 END) as LlamadasAbandonadas'),
                DB::raw('SUM(CASE WHEN c.Origin = 1 THEN 1 ELSE 0 END) as Recibidas_Power911'),
                DB::raw('SUM(CASE WHEN c.Origin = 2 THEN 1 ELSE 0 END) as Creadas_CAD_Manual'),
            ])
            ->join('Agents as a', 'c.Agent', '=', 'a.OID')
            ->whereRaw("c.CreationTime >= '$desdeStr'")
            ->whereRaw("c.CreationTime <= '$hastaStr'")
            ->where(function ($q) {
                $q->where('c.Deleted', 0)
                    ->orWhereNull('c.Deleted');
            })
            ->groupBy(DB::raw('COALESCE(a.DisplayName, a.LogonName)'), 'a.BadgeNumber')
            ->orderByDesc('LlamadasAtendidas')
            ->get();
    }

    /**
     * Resumen estadistico general: total de eventos, promedios, etc.
     * Optimizado: ejecuta los 3 COUNTs en una sola query via UNION ALL con cache de 30 segundos.
     */
    public function getResumenEstadistico(
        Carbon $desde,
        Carbon $hasta,
    ): array {
        $desdeStr = $this->sqlDate($desde);
        $hastaStr = $this->sqlDate($hasta->copy()->addDay());
        $cacheKey = 'resumen_estadistico_'.$desdeStr;

        return Cache::remember($cacheKey, 30, function () use ($desdeStr, $hastaStr) {
            // Una sola query que cuenta los 3 totales via UNION ALL
            // NOLOCK evita bloqueos/deadlocks contra el CAD en produccion
            $resultados = DB::connection('sqlsrv_cad')->select("
                SELECT 'incidentes' as tipo, COUNT(*) as total FROM Incidents WITH (NOLOCK)
                WHERE CreationTime >= '$desdeStr' AND CreationTime <= '$hastaStr'
                AND (Deleted = 0 OR Deleted IS NULL)
                UNION ALL
                SELECT 'llamadas', COUNT(*) FROM Calls WITH (NOLOCK)
                WHERE CreationTime >= '$desdeStr' AND CreationTime <= '$hastaStr'
                AND (Deleted = 0 OR Deleted IS NULL)
                UNION ALL
                SELECT 'despachos', COUNT(*) FROM Responses WITH (NOLOCK)
                WHERE CreationTime >= '$desdeStr' AND CreationTime <= '$hastaStr'
            ");

            $map = collect($resultados)->pluck('total', 'tipo')->toArray();

            return [
                'total_incidentes' => (int) ($map['incidentes'] ?? 0),
                'total_llamadas' => (int) ($map['llamadas'] ?? 0),
                'total_despachos' => (int) ($map['despachos'] ?? 0),
            ];
        });
    }

    /**
     * Estadisticas de incidentes abiertos: sin despacho, sin cerrar, sin recursos asignados.
     * Estados del CAD: 1=Req_Despacho, 6=Terminado, 7=Cerrado.
     * USA CTEs optimizados acotados al dia actual y con cache de 30 segundos.
     */
    public function getEstadisticasIncidentesAbiertos(): array
    {
        $hoy = Carbon::today()->format('Ymd');
        $cacheKey = 'incidentes_abiertos_'.$hoy;

        return Cache::remember($cacheKey, 30, function () use ($hoy) {
            $resultado = DB::connection('sqlsrv_cad')->select("
                WITH base AS (
                    SELECT i.OID, i.Status
                    FROM Incidents i WITH (NOLOCK)
                    WHERE (i.Deleted = 0 OR i.Deleted IS NULL)
                    AND i.CreationTime >= '$hoy'
                ),
                con_resp AS (
                    SELECT DISTINCT Incident FROM Responses WITH (NOLOCK)
                    WHERE CreationTime >= '$hoy'
                ),
                con_asign AS (
                    SELECT DISTINCT r.Incident
                    FROM Responses r WITH (NOLOCK)
                    INNER JOIN Assign a WITH (NOLOCK) ON a.Response = r.OID AND a.Active = 1
                    WHERE r.CreationTime >= '$hoy'
                )
                SELECT
                    (SELECT COUNT(*) FROM base b LEFT JOIN con_resp cr ON b.OID = cr.Incident WHERE cr.Incident IS NULL) as sin_despacho,
                    (SELECT COUNT(*) FROM base b WHERE b.Status NOT IN (6, 7)) as sin_cerrar,
                    (SELECT COUNT(*) FROM base b INNER JOIN con_resp cr ON b.OID = cr.Incident LEFT JOIN con_asign ca ON b.OID = ca.Incident WHERE ca.Incident IS NULL AND b.Status NOT IN (6, 7)) as sin_recursos
            ")[0];

            return [
                'sin_despacho' => (int) ($resultado->sin_despacho ?? 0),
                'sin_cerrar' => (int) ($resultado->sin_cerrar ?? 0),
                'sin_recursos' => (int) ($resultado->sin_recursos ?? 0),
            ];
        });
    }

    /**
     * Cuenta incidentes no cerrados agrupados por tipo de respuesta (ResponseType).
     * Convertido a raw SQL con NOLOCK y cache de 30 segundos.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function getIncidentesPorClasificacion(): array
    {
        $hoy = Carbon::today()->format('Ymd');
        $cacheKey = 'incidentes_clasificacion_'.$hoy;

        return Cache::remember($cacheKey, 30, function () use ($hoy) {
            $resultados = DB::connection('sqlsrv_cad')->select("
                SELECT TOP 10 rt.Name as Tipo, COUNT(DISTINCT i.OID) as Total
                FROM Responses r WITH (NOLOCK)
                INNER JOIN ResponseTypes rt WITH (NOLOCK) ON r.ResponseType = rt.OID
                INNER JOIN Incidents i WITH (NOLOCK) ON r.Incident = i.OID
                WHERE i.Status NOT IN (6, 7)
                AND (i.Deleted = 0 OR i.Deleted IS NULL)
                AND i.CreationTime >= '$hoy'
                GROUP BY rt.Name
                ORDER BY Total DESC
            ");

            $labels = [];
            $data = [];

            foreach ($resultados as $row) {
                $labels[] = $row->Tipo;
                $data[] = (int) $row->Total;
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });
    }

    /**
     * Cuenta incidentes de hoy agrupados por estado del despacho (Responses.Status).
     * Con cache de 30 segundos.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function getIncidentesPorEstado(): array
    {
        $hoy = Carbon::today()->format('Ymd');
        $cacheKey = 'incidentes_estado_'.$hoy;

        return Cache::remember($cacheKey, 30, function () use ($hoy) {
            $resultados = DB::connection('sqlsrv_cad')->select("
                SELECT TOP 15 st.Name as Estado, COUNT(*) as Total
                FROM Responses r WITH (NOLOCK)
                INNER JOIN Incidents i WITH (NOLOCK) ON r.Incident = i.OID
                INNER JOIN Statuses st WITH (NOLOCK) ON r.Status = st.OID
                WHERE (i.Deleted = 0 OR i.Deleted IS NULL)
                AND i.CreationTime >= '$hoy'
                GROUP BY st.Name
                ORDER BY Total DESC
            ");

            $labels = [];
            $data = [];

            foreach ($resultados as $row) {
                $labels[] = $row->Estado;
                $data[] = (int) $row->Total;
            }

            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });
    }

    /**
     * Obtiene el detalle completo de un evento (response) del CAD por su numero de secuencia.
     * Incluye identificacion, ubicacion/coordenadas, contactos, personal, linea de tiempo y
     * duraciones. Se extrajo desde EventReportTable::verDetalle() para reutilizarlo tambien
     * en el modal del widget de Incidentes Activos.
     *
     * Se cachea 10 minutos porque un evento ya consultado no cambia en ese periodo.
     *
     * @param  string  $numeroEvento  Numero de secuencia del response (ej: SE911:2026:09:14:0040)
     * @return object|null Fila con los datos del evento, o null si no existe
     */
    public function getDetalleEvento(string $numeroEvento): ?object
    {
        // Clave de cache por evento (se sanitiza el numero para usarlo como clave de archivo).
        // Se usa el sufijo _v2 para no reutilizar entradas antiguas sin el numero formateado.
        $cacheClave = 'evento_detalle_v2_'.preg_replace('/[^a-zA-Z0-9_:]/', '', $numeroEvento);

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

        $evento = $detalle[0] ?? null;

        // Agrega el numero de incidente formateado (SE911:AAAA:MM:DD:NNNNNN) para mostrar en el modal.
        // Se calcula aqui (dentro del cache) para que el modal lo tenga listo sin logica extra.
        if ($evento) {
            $incSeq = $evento->{'Numero Incidente'} ?? null;
            $creacion = $evento->{'Hora Creacion'} ?? null;
            $numero = null;
            if ($incSeq !== null && $incSeq !== '') {
                $partesInc = explode(':', (string) $incSeq);
                $numero = end($partesInc);
            }
            $fecha = $creacion ? Carbon::parse($creacion) : now();
            $evento->{'Numero Incidente Formateado'} = $numero !== null
                ? "SE911:{$fecha->format('Y')}:{$fecha->format('m')}:{$fecha->format('d')}:{$numero}"
                : ($evento->{'Numero de Evento'} ?? '');
        }

        return $evento;
    }

    /**
     * Obtiene la cronologia de notas de un evento (ResponseNotes) ordenadas por fecha.
     * Combina notas del incidente y del despacho especifico con UNION. Cacheado 10 minutos
     * para abrir el modal de forma instantanea.
     *
     * @param  string  $numeroEvento  Numero de secuencia del response (ej: SE911:2026:09:14:0040)
     * @return array<int, object> Lista de notas con fecha, operador, estacion y texto
     */
    public function getNotasEvento(string $numeroEvento): array
    {
        // Misma clave base que el detalle, con sufijo _notas (v2 por consistencia)
        $cacheClave = 'evento_detalle_v2_'.preg_replace('/[^a-zA-Z0-9_:]/', '', $numeroEvento);

        return Cache::remember($cacheClave.'_notas', 600, function () use ($numeroEvento) {
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
    }
}
