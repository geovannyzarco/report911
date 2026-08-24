<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Tiempos Activos de Recursos (VMIS_RESP_RESOACTIVETIMES).
 * Calcula el tiempo activo de cada recurso asignado a un despacho.
 * Tabla de ~2.4 millones de filas.
 */
class RespResoActiveTime extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'VMIS_RESP_RESOACTIVETIMES';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'ASSIGN',
        'RESPONSE',
        'RESOURCE',
        'UTCTIME_START',
        'UTCTIME_END',
    ];

    protected $casts = [
        'UTCTIME_START' => 'datetime',
        'UTCTIME_END' => 'datetime',
    ];

    public function assign(): BelongsTo
    {
        return $this->belongsTo(Assign::class, 'ASSIGN', 'OID');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class, 'RESPONSE', 'OID');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'RESOURCE', 'OID');
    }
}
