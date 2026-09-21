<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\EstadoRecurso;
use App\Models\Sector;
use App\Models\TipoVehiculo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Poblado inicial de los catalogos del modulo "Recursos por Turno".
 *
 * Idempotente: usa firstOrCreate por nombre para poder re-ejecutarse sin duplicar filas.
 */
class CatalogoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedSectores();
        $this->seedCategorias();
        $this->seedTiposVehiculos();
        $this->seedEstadosRecursos();
    }

    /** Delegaciones y puestos de la PNC donde operan recursos y despachadores. */
    private function seedSectores(): void
    {
        $sectores = [
            'Delegacion Central',
            'Delegacion San Miguel',
            'Delegacion Santa Ana',
            'Delegacion Sonsonate',
            'Delegacion La Libertad',
            'Delegacion La Union',
            'Delegacion Usulutan',
            'Delegacion Cabanas',
            'Delegacion Chalatenango',
            'Delegacion Cuscatlan',
            'Delegacion Morazan',
            'Delegacion San Vicente',
            'Delegacion La Paz',
            'Delegacion Ahuachapan',
        ];

        foreach ($sectores as $nombre) {
            Sector::firstOrCreate(['nombre' => $nombre]);
        }
    }

    /** Grados jerarquicos del personal policial. */
    private function seedCategorias(): void
    {
        $categorias = [
            'Agente',
            'Cabo',
            'Sargento',
            'Subinspector',
            'Inspector',
            'Subcomisionado',
            'Comisionado',
        ];

        foreach ($categorias as $nombre) {
            Categoria::firstOrCreate(['nombre' => $nombre]);
        }
    }

    /** Tipos de vehiculos disponibles para los turnos. */
    private function seedTiposVehiculos(): void
    {
        $tipos = [
            'Moto',
            'Patrulla',
            'Camion',
            'Pickup',
            'Ranchera',
            'Bicicleta',
            'Lancha',
            'Helicoptero',
        ];

        foreach ($tipos as $nombre) {
            TipoVehiculo::firstOrCreate(['nombre' => $nombre]);
        }
    }

    /** Estados posibles de un recurso dentro de un turno. */
    private function seedEstadosRecursos(): void
    {
        $estados = [
            'Disponible',
            'No Disponible',
            'En Mision',
            'En Mision Especial',
            'En Comision',
            'En Mantenimiento',
            'Daniado',
            'Vacaciones',
            'Permiso',
        ];

        foreach ($estados as $nombre) {
            EstadoRecurso::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
