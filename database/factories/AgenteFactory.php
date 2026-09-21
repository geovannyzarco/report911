<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\Categoria;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agente>
 */
class AgenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'oni' => (string) fake()->unique()->numberBetween(1000000000, 9999999999),
            'nombre' => fake()->name(),
            'categoria_id' => Categoria::factory(),
            'sector_id' => Sector::factory(),
            'telefono_oni' => fake()->unique()->numerify('5037#######'),
            'activo' => true,
        ];
    }
}
