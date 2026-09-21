<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de categorias de Filament.
 */
class CreateCategoria extends CreateRecord
{
    protected static string $resource = CategoriaResource::class;
}
