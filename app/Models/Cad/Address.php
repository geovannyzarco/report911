<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Direcciones del sistema CAD TiburonCad.
 * Almacena direcciones normalizadas y coordenadas GIS.
 */
class Address extends Model
{
    protected $connection = 'sqlsrv_cad';

    protected $table = 'Addresses';

    protected $primaryKey = 'OID';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'OID',
        'Street',
        'IntersectionStreet1',
        'IntersectionStreet2',
        'CommonPlace',
        'FreeFormatAddress',
        'XCoordinate',
        'YCoordinate',
    ];

    protected $casts = [
        'XCoordinate' => 'float',
        'YCoordinate' => 'float',
    ];
}
