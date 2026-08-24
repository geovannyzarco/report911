<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Asignación de Unidades del sistema CAD TiburonCad.
 * Vincula un recurso a una respuesta activa.
 */
class Assign extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Assign';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Resource',
        'Response',
        'Active',
        'TimeStamp1',
    ];

    protected $casts = [
        'Active' => 'boolean',
        'TimeStamp1' => 'datetime',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'Resource', 'OID');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class, 'Response', 'OID');
    }
}
