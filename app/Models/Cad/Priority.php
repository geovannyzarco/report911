<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Prioridades del sistema CAD TiburonCad.
 * Catálogo de prioridades de emergencias.
 */
class Priority extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Priorities';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
        'Rank',
    ];
}
