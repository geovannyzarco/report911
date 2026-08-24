<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Recursos/Unidades del sistema CAD TiburonCad.
 * Representa vehículos, patrullas, ambulancias, etc.
 */
class Resource extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Resources';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
        'Status',
        'ActiveResponse',
        'ActiveIncident',
        'Station',
        'CurrentAddress',
        'DispatchGroup',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'Status', 'OID');
    }

    public function activeResponse(): BelongsTo
    {
        return $this->belongsTo(Response::class, 'ActiveResponse', 'OID');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'Station', 'OID');
    }
}
