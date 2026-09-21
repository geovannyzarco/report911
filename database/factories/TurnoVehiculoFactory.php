<?php

namespace Database\Factories;

use App\Models\EstadoRecurso;
use App\Models\Turno;
use App\Models\TurnoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TurnoVehiculo>
 */
class TurnoVehiculoFactory extends Factory
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
            'vehiculo_id' => Vehiculo::factory(),
            'estado_recurso_id' => EstadoRecurso::factory(),
            'nota' => fake()->optional(0.8)->sentence(),
        ];
    }
}
