<?php

namespace App\Filament\Resources\EstadoRecursoResource\Pages;

use App\Filament\Resources\EstadoRecursoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de estados de recurso de Filament.
 */
class ListEstadoRecursos extends ListRecords
{
    protected static string $resource = EstadoRecursoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
