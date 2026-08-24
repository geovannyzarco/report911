<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Clasificaciones del sistema CAD TiburonCad.
 * Catálogo de tipos de incidente (Robo, Accidente, etc.).
 */
class Classification extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Classifications';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
        'ShortName',
    ];
}
