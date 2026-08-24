<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Estados del sistema CAD TiburonCad.
 * Catalogo de estados para respuestas, incidentes y recursos.
 */
class Status extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Statuses';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
        'ShortName',
        'StatusType',
        'ActionType',
    ];
}
