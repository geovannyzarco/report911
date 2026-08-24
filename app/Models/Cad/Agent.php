<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Agentes/Operadores del sistema CAD TiburonCad.
 * Representa telefonistas y despachadores.
 */
class Agent extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Agents';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Name',
        'UserId',
        'Station',
        'AgentType',
    ];
}
