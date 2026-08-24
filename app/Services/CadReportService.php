<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de reportes para la base de datos CAD ViperCAD_Log.
 * Encapsula todas las queries optimizadas de SQL Server.
 */
class CadReportService
{
    private const OVERFLOW_VALUE = 2147483647;

    /**
     * Reporte de tiempos de respuesta promedio por agencia, prioridad y tipo de incidente.
     * Calcula tiempos de despacho, en ruta, tránsito y en escena.
     */
    public function getTiemposRespuestaPromedio(
        Carbon $desde,
        Carbon $hasta,
        ?string $agencia = null,
        ?string $prioridad = null,
    ): Collection {
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
            ->where('sc.STATUSTIME', '>=', $desde)
            ->where('sc.STATUSTIME', '<=', $hasta)
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
     * Reporte de volumen de incidentes por clasificación y prioridad.
     */
    public function getVolumenIncidentes(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
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
            ->where('i.CreationTime', '>=', $desde)
            ->where('i.CreationTime', '<=', $hasta)
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
     * Reporte de utilización y eficiencia de unidades (recursos).
     * Mide despachos totales y horas de servicio activo por unidad.
     */
    public function getUtilizacionUnidades(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
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
            ->where('rat.UTCTIME_START', '>=', $desde)
            ->where('rat.UTCTIME_START', '<=', $hasta)
            ->groupBy('res.Name', 'sta.Name')
            ->orderByDesc('HorasServicioActivo')
            ->get();
    }

    /**
     * Reporte de códigos de cierre / disposiciones de emergencias.
     */
    public function getDisposicionesCierre(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
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
            ->where('r.CreationTime', '>=', $desde)
            ->where('r.CreationTime', '<=', $hasta)
            ->groupBy('ag.Name', 'rt.Name', 'dc.Name')
            ->orderBy('Agencia')
            ->orderByDesc('Cantidad')
            ->get();
    }

    /**
     * Reporte de desempeño y carga de trabajo de telefonistas/operadores.
     */
    public function getDesempenoOperadores(
        Carbon $desde,
        Carbon $hasta,
    ): Collection {
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
            ->where('c.CreationTime', '>=', $desde)
            ->where('c.CreationTime', '<=', $hasta)
            ->where(function ($q) {
                $q->where('c.Deleted', 0)
                    ->orWhereNull('c.Deleted');
            })
            ->groupBy(DB::raw('COALESCE(a.DisplayName, a.LogonName)'), 'a.BadgeNumber')
            ->orderByDesc('LlamadasAtendidas')
            ->get();
    }

    /**
     * Resumen estadístico general: total de eventos, promedios, etc.
     */
    public function getResumenEstadistico(
        Carbon $desde,
        Carbon $hasta,
    ): array {
        $totalIncidentes = DB::connection('sqlsrv_cad')->table('Incidents')
            ->where('CreationTime', '>=', $desde)
            ->where('CreationTime', '<=', $hasta)
            ->where(function ($q) {
                $q->where('Deleted', 0)->orWhereNull('Deleted');
            })
            ->count();

        $totalLlamadas = DB::connection('sqlsrv_cad')->table('Calls')
            ->where('CreationTime', '>=', $desde)
            ->where('CreationTime', '<=', $hasta)
            ->where(function ($q) {
                $q->where('Deleted', 0)->orWhereNull('Deleted');
            })
            ->count();

        $totalDespachos = DB::connection('sqlsrv_cad')->table('Responses')
            ->where('CreationTime', '>=', $desde)
            ->where('CreationTime', '<=', $hasta)
            ->count();

        return [
            'total_incidentes' => $totalIncidentes,
            'total_llamadas' => $totalLlamadas,
            'total_despachos' => $totalDespachos,
        ];
    }
}
