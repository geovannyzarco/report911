<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Agencias del sistema CAD TiburonCad.
 * Representa las agencias que responden (PNC, Cruz Roja, Bomberos, etc.).
 */
class Agency extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Agencies';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
        'ShortName',
    ];
}
