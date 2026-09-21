<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catalogo de sectores: delegaciones y puestos donde operan los recursos y despachadores.
 */
class Sector extends Model
{
    protected $fillable = ['nombre'];

    /** Sectores que agrupan a los agentes destacados en ellos (uno a muchos). */
    public function agentes(): HasMany
    {
        return $this->hasMany(Agente::class);
    }

    /** Sectores donde se ubican los vehiculos asignados (uno a muchos). */
    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class);
    }

    /** Sectores a los que pertenecen los despachadores (uno a muchos). */
    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class);
    }
}
