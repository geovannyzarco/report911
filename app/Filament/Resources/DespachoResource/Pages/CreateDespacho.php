<?php

namespace App\Filament\Resources\DespachoResource\Pages;

use App\Filament\Resources\DespachoResource;
use App\Services\DespachoService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Página de creación de despachadores: da de alta usuario y despacho vía DespachoService.
 */
class CreateDespacho extends CreateRecord
{
    protected static string $resource = DespachoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(DespachoService::class)->darDeAlta($data);
    }
}
