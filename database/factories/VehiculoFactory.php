<?php

namespace Database\Factories;

use App\Models\Sector;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehiculo>
 */
class VehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero_equipo' => strtoupper(fake()->unique()->bothify('EQ-###-??')),
            'tipo_vehiculo_id' => TipoVehiculo::factory(),
            'sector_id' => Sector::factory(),
            'activo' => true,
        ];
    }
}
