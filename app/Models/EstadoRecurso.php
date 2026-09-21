<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catalogo de estados de un recurso dentro de un turno (Disponible, No Disponible, En Mision, etc.).
 */
class EstadoRecurso extends Model
{
    protected $fillable = ['nombre'];

    /** Estados usados en el pivot de agentes por turno (uno a muchos). */
    public function turnoAgentes(): HasMany
    {
        return $this->hasMany(TurnoAgente::class);
    }

    /** Estados usados en el pivot de vehiculos por turno (uno a muchos). */
    public function turnoVehiculos(): HasMany
    {
        return $this->hasMany(TurnoVehiculo::class);
    }
}
