<?php

namespace App\Filament\Resources\AgenteResource\Pages;

use App\Filament\Resources\AgenteResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de agentes de Filament.
 */
class CreateAgente extends CreateRecord
{
    protected static string $resource = AgenteResource::class;
}
