<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot turno-vehiculo: guarda el estado y la nota del vehiculo dentro de un turno
 * (ej. recurso no disponible, mision asignada, etc.).
 */
class TurnoVehiculo extends Model
{
    protected $fillable = [
        'turno_id',
        'vehiculo_id',
        'estado_recurso_id',
        'nota',
    ];

    /** Turno al que pertenece el registro (N-a-1). */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    /** Vehiculo incluido en el turno (N-a-1). */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    /** Estado del vehiculo en ese turno (N-a-1). */
    public function estadoRecurso(): BelongsTo
    {
        return $this->belongsTo(EstadoRecurso::class);
    }
}
