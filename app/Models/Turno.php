<?php

namespace App\Models;

use Database\Factories\TurnoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Turno de un despacho: agrupa los recursos (agentes y vehiculos)
 * que estuvieron disponibles durante el periodo inicio-fin.
 */
class Turno extends Model
{
    /** @use HasFactory<TurnoFactory> */
    use HasFactory;

    protected $fillable = [
        'despacho_id',
        'inicio',
        'fin',
    ];

    protected function casts(): array
    {
        return [
            'inicio' => 'datetime',
            'fin' => 'datetime',
        ];
    }

    /** Despachador que registro el turno (pertenece a un despacho). */
    public function despacho(): BelongsTo
    {
        return $this->belongsTo(Despacho::class);
    }

    /** Agentes incluidos en el turno con su estado y nota (uno a muchos). */
    public function turnoAgentes(): HasMany
    {
        return $this->hasMany(TurnoAgente::class);
    }

    /** Vehiculos incluidos en el turno con su estado y nota (uno a muchos). */
    public function turnoVehiculos(): HasMany
    {
        return $this->hasMany(TurnoVehiculo::class);
    }

    /** Agentes del turno (relacion N-N a traves del pivot). */
    public function agentes(): BelongsToMany
    {
        return $this->belongsToMany(Agente::class, 'turno_agentes')
            ->withPivot(['estado_recurso_id', 'nota']);
    }

    /** Vehiculos del turno (relacion N-N a traves del pivot). */
    public function vehiculos(): BelongsToMany
    {
        return $this->belongsToMany(Vehiculo::class, 'turno_vehiculos')
            ->withPivot(['estado_recurso_id', 'nota']);
    }
}
