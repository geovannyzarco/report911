<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Llamantes del sistema CAD TiburonCad.
 * Almacena información de quien realizó la llamada al 911.
 */
class Caller extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Callers';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'CPIAddress',
        'CallerType',
        'PhoneNumber',
        'PhoneOwnerName',
    ];
}
