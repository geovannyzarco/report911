<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catalogo maestro de vehiculos: recurso identificado por numero de equipo,
 * clasificado por tipo (moto, patrulla, lancha, etc.) y sector de operacion.
 */
class Vehiculo extends Model
{
    protected $fillable = [
        'numero_equipo',
        'tipo_vehiculo_id',
        'sector_id',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /** Tipo de vehiculo al que pertenece (moto, patrulla, etc.). */
    public function tipoVehiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class);
    }

    /** Sector de operacion del vehiculo (opcional). */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /** Registros del vehiculo en los turnos (uno a muchos). */
    public function turnoVehiculos(): HasMany
    {
        return $this->hasMany(TurnoVehiculo::class);
    }

    /** Turnos en los que participo el vehiculo, con estado y nota del pivot. */
    public function turnos(): BelongsToMany
    {
        return $this->belongsToMany(Turno::class, 'turno_vehiculos')
            ->withPivot(['estado_recurso_id', 'nota']);
    }
}
