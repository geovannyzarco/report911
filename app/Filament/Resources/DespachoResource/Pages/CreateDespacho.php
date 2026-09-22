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
        // Los MultiSelect de relación (many-to-many) no se incluyen en $data
        // (Filament los excluye y los sincroniza solo); aquí se recuperan del
        // estado en bruto del formulario para asignarlos vía el servicio.
        $data['sectores'] = $this->form->getRawState()['sectores'] ?? [];

        return app(DespachoService::class)->darDeAlta($data);
    }
}
