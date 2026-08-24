<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de monitoreo en vivo para la base de datos CAD ViperCAD_Log.
 * Consultas de estado actual optimizadas para tiempo real.
 */
class CadMonitorService
{
    /**
     * Obtiene los incidentes activos (no cerrados ni finalizados).
     */
    public function getEventosActivos(): Collection
    {
        return DB::connection('sqlsrv_cad')->table('Incidents as i')
            ->select([
                'i.OID',
                'i.SequenceNumber',
                'i.CreationTime',
                'cl.Name as Clasificacion',
                'pr.Name as Prioridad',
                'st.Name as Estado',
                'ag.Name as Agencia',
                DB::raw('COALESCE(a.DisplayName, a.LogonName) as Operador'),
            ])
            ->leftJoin('Classifications as cl', 'i.Classification', '=', 'cl.OID')
            ->leftJoin('Priorities as pr', 'i.Priority', '=', 'pr.OID')
            ->leftJoin('Statuses as st', 'i.Status', '=', 'st.OID')
            ->leftJoin('Agencies as ag', 'i.PrimaryAgency', '=', 'ag.OID')
            ->leftJoin('Agents as a', 'i.Agent', '=', 'a.OID')
            ->whereNotIn('i.Status', [6, 7]) // No Terminado, No Cerrado
            ->where(function ($q) {
                $q->where('i.Deleted', 0)->orWhereNull('i.Deleted');
            })
            ->orderByDesc('i.CreationTime')
            ->get();
    }

    /**
     * Obtiene las unidades/recursos actualmente asignadas a incidentes activos.
     */
    public function getRecursosEnCampo(): Collection
    {
        return DB::connection('sqlsrv_cad')->table('Resources as res')
            ->select([
                'res.OID',
                'res.Name as CodigoUnidad',
                'st.Nombre as EstadoUnidad',
                'sta.Name as Estacion',
                'i.SequenceNumber as Incidente',
                'rt.Name as TipoRespuesta',
            ])
            ->leftJoin('Statuses as st', 'res.Status', '=', 'st.OID')
            ->leftJoin('Stations as sta', 'res.Station', '=', 'sta.OID')
            ->leftJoin('Responses as r', 'res.ActiveResponse', '=', 'r.OID')
            ->leftJoin('Incidents as i', 'res.ActiveIncident', '=', 'i.OID')
            ->leftJoin('ResponseTypes as rt', 'r.ResponseType', '=', 'rt.OID')
            ->where('res.ActiveResponse', '!=', 0)
            ->whereNotNull('res.ActiveResponse')
            ->orderBy('res.Name')
            ->get();
    }

    /**
     * Obtiene los últimos eventos de los últimos N minutos.
     */
    public function getUltimosEventosRecientes(int $minutos = 30): Collection
    {
        $desde = Carbon::now()->subMinutes($minutos);

        return DB::connection('sqlsrv_cad')->table('VMIS_RESP_STATUSCHANGES as sc')
            ->select([
                'sc.RESPONSE',
                'sc.STATUSTIME',
                'st.Name as EstadoAnterior',
                'st2.Name as EstadoNuevo',
                'sc.ELAPSEDTIME_MS',
                'r.SequenceNumber as Despacho',
                'i.SequenceNumber as Incidente',
            ])
            ->join('Responses as r', 'sc.RESPONSE', '=', 'r.OID')
            ->leftJoin('Incidents as i', 'r.Incident', '=', 'i.OID')
            ->leftJoin('Statuses as st', 'sc.STATUS', '=', 'st.OID')
            ->leftJoin('Statuses as st2', 'sc.NEXTSTATUS', '=', 'st2.OID')
            ->where('sc.STATUSTIME', '>=', $desde)
            ->orderByDesc('sc.STATUSTIME')
            ->limit(100)
            ->get();
    }
}
