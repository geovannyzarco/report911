<?php

namespace App\Filament\Resources\TipoVehiculoResource\Pages;

use App\Filament\Resources\TipoVehiculoResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de tipos de vehiculo de Filament.
 */
class CreateTipoVehiculo extends CreateRecord
{
    protected static string $resource = TipoVehiculoResource::class;
}
