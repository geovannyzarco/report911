<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Turno>
 */
class TurnoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'despacho_id' => Despacho::factory(),
            'inicio' => now()->subHours(3),
            'fin' => now()->subHours(2),
        ];
    }
}
