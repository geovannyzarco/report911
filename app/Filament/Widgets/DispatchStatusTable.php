<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget: DispatchStatusTable
 * Nombre: Estado de Despachos (Tabla)
 * Descripcion: Muestra una tabla con los estados de despacho y sus cantidades del dia.
 * Usa el metodo records() de Filament para datos personalizados.
 */
class DispatchStatusTable extends BaseWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    protected static ?string $heading = 'Detalle de Despachos por Estado';

    protected static ?int $sort = 4;

    protected static string|false $pollingInterval = false;

    /**
     * Define la estructura de la tabla.
     */
    public function table(Table $table): Table
    {
        $stats = (new CadReportService)->getIncidentesPorEstado();

        // Convierte los datos a un array indexado para Filament
        $records = [];
        foreach ($stats['labels'] as $index => $estado) {
            $records[$index] = [
                'id' => $index,
                'estado' => $estado,
                'cantidad' => $stats['data'][$index] ?? 0,
            ];
        }

        return $table
            ->records(fn () => $records)
            ->columns([
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Cerrado' => 'success',
                        'Terminado' => 'info',
                        'En Sitio' => 'danger',
                        'En Ruta' => 'warning',
                        'Req_Despacho' => 'gray',
                        'Despachado' => 'info',
                        default => 'gray',
                    })
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
            ])
            ->defaultSort('cantidad', 'desc')
            ->paginated(false);
    }
}
