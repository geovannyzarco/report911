<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot turno-agente: guarda el estado y la nota del agente dentro de un turno
 * (ej. recurso no disponible, mision asignada, etc.).
 */
class TurnoAgente extends Model
{
    protected $fillable = [
        'turno_id',
        'agente_id',
        'estado_recurso_id',
        'nota',
    ];

    /** Turno al que pertenece el registro (N-a-1). */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    /** Agente incluido en el turno (N-a-1). */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    /** Estado del agente en ese turno (N-a-1). */
    public function estadoRecurso(): BelongsTo
    {
        return $this->belongsTo(EstadoRecurso::class);
    }
}
