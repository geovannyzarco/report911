<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: EventReport
 * Modelo temporal para el reporte de eventos del CAD.
 * Usa una vista/subquery para obtener los datos paginados.
 */
class EventReport extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Responses';

    protected $primaryKey = 'OID';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Scope para filtrar por rango de fechas y obtener datos del reporte.
     */
    public function scopeForReport(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->select(
            'SequenceNumber as numero_evento',
            'OID as id'
        )->whereBetween('CreationTime', [$desde, $hasta]);
    }
}
