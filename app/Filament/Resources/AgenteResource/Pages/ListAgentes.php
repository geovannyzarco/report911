<?php

namespace App\Filament\Resources\AgenteResource\Pages;

use App\Filament\Resources\AgenteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de agentes de Filament.
 */
class ListAgentes extends ListRecords
{
    protected static string $resource = AgenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
