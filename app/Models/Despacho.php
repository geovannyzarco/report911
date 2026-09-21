<?php

namespace App\Models;

use Database\Factories\DespachoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Despachador del modulo Recursos por Turno: se enlaza 1-a-1 (user_id unico)
 * con el usuario creado automaticamente en "users" para el acceso al panel.
 */
class Despacho extends Model
{
    /** @use HasFactory<DespachoFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'oni',
        'nombre',
        'categoria_id',
        'sector_id',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /** Usuario de acceso del panel asociado al despachador (1-a-1). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Rango policial del despachador. */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** Sector (delegacion/puesto) al que pertenece el despachador. */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /** Turnos registrados por el despachador (uno a muchos). */
    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }
}
