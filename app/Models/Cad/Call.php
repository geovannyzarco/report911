<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Llamadas del sistema CAD TiburonCad.
 * Conecta a la base de datos SQL Server ViperCAD_Log.
 */
class Call extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Calls';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Incident',
        'Caller',
        'ALIAddress',
        'Agent',
        'CallState',
        'Origin',
        'CreationTime',
        'SequenceNumber',
        'Ani',
    ];

    protected $casts = [
        'CreationTime' => 'datetime',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class, 'Incident', 'OID');
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(Caller::class, 'Caller', 'OID');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'Agent', 'OID');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'ALIAddress', 'OID');
    }
}
