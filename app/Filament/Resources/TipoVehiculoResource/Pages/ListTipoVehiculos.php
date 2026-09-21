<?php

namespace App\Filament\Resources\TipoVehiculoResource\Pages;

use App\Filament\Resources\TipoVehiculoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de tipos de vehiculo de Filament.
 */
class ListTipoVehiculos extends ListRecords
{
    protected static string $resource = TipoVehiculoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
