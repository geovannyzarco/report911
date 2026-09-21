<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catalogo de categorias: rangos policiales (Agente, Cabo, Sargento, etc.).
 */
class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    protected $fillable = ['nombre'];

    /** Rango al que pertenecen los agentes (uno a muchos). */
    public function agentes(): HasMany
    {
        return $this->hasMany(Agente::class);
    }

    /** Rango de los despachadores (uno a muchos). */
    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class);
    }
}
