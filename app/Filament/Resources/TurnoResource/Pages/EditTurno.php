<?php

namespace App\Filament\Resources\TurnoResource\Pages;

use App\Filament\Resources\TurnoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de turnos de Filament.
 */
class EditTurno extends EditRecord
{
    protected static string $resource = TurnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
