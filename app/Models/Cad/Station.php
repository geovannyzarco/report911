<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Estaciones del sistema CAD TiburonCad.
 * Estaciones base de procedencia de los recursos.
 */
class Station extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Stations';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
    ];
}
