<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
     */
    public function getResumenEstadistico(
        Carbon $desde,
        Carbon $hasta,
    ): array {
        $desdeStr = $this->sqlDate($desde);
        $hastaStr = $this->sqlDate($hasta->copy()->addDay());

        $totalIncidentes = DB::connection('sqlsrv_cad')->table('Incidents')
            ->whereRaw("CreationTime >= '$desdeStr'")
            ->whereRaw("CreationTime <= '$hastaStr'")
            ->where(function ($q) {
                $q->where('Deleted', 0)->orWhereNull('Deleted');
            })
            ->count();

        $totalLlamadas = DB::connection('sqlsrv_cad')->table('Calls')
            ->whereRaw("CreationTime >= '$desdeStr'")
            ->whereRaw("CreationTime <= '$hastaStr'")
            ->where(function ($q) {
                $q->where('Deleted', 0)->orWhereNull('Deleted');
            })
            ->count();

        $totalDespachos = DB::connection('sqlsrv_cad')->table('Responses')
            ->whereRaw("CreationTime >= '$desdeStr'")
            ->whereRaw("CreationTime <= '$hastaStr'")
            ->count();

        return [
            'total_incidentes' => $totalIncidentes,
            'total_llamadas' => $totalLlamadas,
            'total_despachos' => $totalDespachos,
        ];
    }

    /**
     * Estadisticas de incidentes abiertos: sin despacho, sin cerrar, sin recursos asignados.
     * Estados del CAD: 1=Req_Despacho, 6=Terminado, 7=Cerrado.
     */
    public function getEstadisticasIncidentesAbiertos(): array
    {
        $connection = DB::connection('sqlsrv_cad');
        $hoy = Carbon::today()->format('Ymd');

        // Incidentes de hoy sin despacho: no tienen ningun Response asociado
        $sinDespacho = $connection->table('Incidents as i')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('Responses as r')
                    ->whereRaw('r.Incident = i.OID');
            })
            ->where(function ($q) {
                $q->where('i.Deleted', 0)->orWhereNull('i.Deleted');
            })
            ->whereRaw("i.CreationTime >= '$hoy'")
            ->count();

        // Incidentes de hoy sin cerrar: status no es Terminado(6) ni Cerrado(7)
        $sinCerrar = $connection->table('Incidents as i')
            ->whereNotIn('i.Status', [6, 7])
            ->where(function ($q) {
                $q->where('i.Deleted', 0)->orWhereNull('i.Deleted');
            })
            ->whereRaw("i.CreationTime >= '$hoy'")
            ->count();

        // Incidentes de hoy sin recursos asignados: tienen al menos un Response pero
        // ninguno tiene unidades asignadas (tabla Assign con Active=1)
        $sinRecursos = $connection->table('Incidents as i')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('Responses as r')
                    ->whereRaw('r.Incident = i.OID');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('Responses as r')
                    ->join('Assign as a', 'a.Response', '=', 'r.OID')
                    ->whereRaw('r.Incident = i.OID')
                    ->where('a.Active', 1);
            })
            ->where(function ($q) {
                $q->where('i.Deleted', 0)->orWhereNull('i.Deleted');
            })
            ->whereNotIn('i.Status', [6, 7])
            ->whereRaw("i.CreationTime >= '$hoy'")
            ->count();

        return [
            'sin_despacho' => $sinDespacho,
            'sin_cerrar' => $sinCerrar,
            'sin_recursos' => $sinRecursos,
        ];
    }
}
