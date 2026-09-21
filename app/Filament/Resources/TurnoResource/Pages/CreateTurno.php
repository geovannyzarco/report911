<?php

namespace App\Filament\Resources\TurnoResource\Pages;

use App\Filament\Resources\TurnoResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de turnos de Filament.
 */
class CreateTurno extends CreateRecord
{
    protected static string $resource = TurnoResource::class;
}
