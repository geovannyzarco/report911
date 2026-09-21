<?php

namespace App\Models;

use Database\Factories\AgenteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catalogo maestro de agentes: recurso identificado por ONI, con rango,
 * sector (delegacion/puesto) y telefono ONI de contacto en sitio.
 */
class Agente extends Model
{
    /** @use HasFactory<AgenteFactory> */
    use HasFactory;

    protected $fillable = [
        'oni',
        'nombre',
        'categoria_id',
        'sector_id',
        'telefono_oni',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /** Rango policial del agente (pertenece a una categoria). */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** Sector (delegacion/puesto) donde esta destacado el agente. */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /** Registros del agente en los turnos (uno a muchos). */
    public function turnoAgentes(): HasMany
    {
        return $this->hasMany(TurnoAgente::class);
    }

    /** Turnos en los que participo el agente, con estado y nota del pivot. */
    public function turnos(): BelongsToMany
    {
        return $this->belongsToMany(Turno::class, 'turno_agentes')
            ->withPivot(['estado_recurso_id', 'nota']);
    }
}
