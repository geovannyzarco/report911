<?php

namespace Database\Factories;

use App\Models\EstadoRecurso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstadoRecurso>
 */
class EstadoRecursoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Disponible', 'No Disponible', 'En Mision', 'En Comision', 'En Mantenimiento', 'Daniado']),
        ];
    }
}
