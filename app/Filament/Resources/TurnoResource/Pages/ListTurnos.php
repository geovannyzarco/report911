<?php

namespace App\Filament\Resources\TurnoResource\Pages;

use App\Filament\Resources\TurnoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de turnos de Filament.
 */
class ListTurnos extends ListRecords
{
    protected static string $resource = TurnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
