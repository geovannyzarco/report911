<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Cambios de Estado de Respuestas (VMIS_RESP_STATUSCHANGES).
 * Registra cada cambio de estado de una respuesta con su duración en ms.
 * Tabla de ~17.6 millones de filas.
 */
class RespStatusChange extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'VMIS_RESP_STATUSCHANGES';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Valor de desbordamiento (Int32.MaxValue) que indica estado abierto.
     * Se debe filtrar en reportes de promedios de tiempo.
     */
    const OVERFLOW_VALUE = 2147483647;

    protected $fillable = [
        'OID',
        'RESPONSE',
        'STATUS',
        'STATUSTIME',
        'NEXTSTATUS',
        'ELAPSEDTIME_MS',
    ];

    protected $casts = [
        'STATUSTIME' => 'datetime',
        'ELAPSEDTIME_MS' => 'integer',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class, 'RESPONSE', 'OID');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'STATUS', 'OID');
    }

    public function nextStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'NEXTSTATUS', 'OID');
    }

    /**
     * Scope para filtrar registros válidos (sin valor de desbordamiento).
     */
    public function scopeValid($query)
    {
        return $query->where('ELAPSEDTIME_MS', '<', self::OVERFLOW_VALUE);
    }
}
