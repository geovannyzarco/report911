<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\EstadoRecurso;
use App\Models\Turno;
use App\Models\TurnoAgente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TurnoAgente>
 */
class TurnoAgenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'turno_id' => Turno::factory(),
            'agente_id' => Agente::factory(),
            'estado_recurso_id' => EstadoRecurso::factory(),
            'nota' => fake()->optional(0.8)->sentence(),
        ];
    }
}
