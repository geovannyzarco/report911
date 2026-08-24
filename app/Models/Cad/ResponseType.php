<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Tipos de Respuesta del sistema CAD TiburonCad.
 * Catálogo de tipos de despacho (Patrulla, Ambulancia, etc.).
 */
class ResponseType extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'ResponseTypes';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
        'ShortName',
    ];
}
