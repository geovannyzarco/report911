<?php

namespace App\Models;

use Database\Factories\TipoVehiculoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catalogo de tipos de vehiculo registrados como recursos (Moto, Patrulla, Lancha, etc.).
 */
class TipoVehiculo extends Model
{
    /** @use HasFactory<TipoVehiculoFactory> */
    use HasFactory;

    protected $fillable = ['nombre'];

    /** Tipos que clasifican a los vehiculos (uno a muchos). */
    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class);
    }
}
