<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Despacho;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Despacho>
 */
class DespachoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'oni' => (string) fake()->unique()->numberBetween(1000000000, 9999999999),
            'nombre' => fake()->name(),
            'categoria_id' => Categoria::factory(),
            'activo' => true,
        ];
    }

    /**
     * Estado que adjunta los sectores indicados al despachador (relacion muchos-a-muchos).
     */
    public function withSectores(int $count = 1): static
    {
        return $this->afterCreating(function (Despacho $despacho) use ($count): void {
            $sectores = Sector::factory()->count($count)->create();
            $despacho->sectores()->attach($sectores);
        });
    }
}
