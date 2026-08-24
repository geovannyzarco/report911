<?php

namespace App\Filament\Widgets;

use App\Services\CadMonitorService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget que muestra las unidades/recursos actualmente en campo.
 */
class FieldResourcesWidget extends BaseWidget
{
    protected static ?string $heading = 'Unidades en Campo';

    protected static ?int $sort = 3;

    protected static string|false $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => (new CadMonitorService)->getRecursosEnCampo())
            ->columns([
                Tables\Columns\TextColumn::make('CodigoUnidad')
                    ->label('Unidad')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('EstadoUnidad')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Despachado') => 'warning',
                        str_contains($state, 'En Ruta') => 'info',
                        str_contains($state, 'En Sitio') => 'success',
                        str_contains($state, 'Fuera De Turno') => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('Estacion')
                    ->label('Estacion')
                    ->limit(30),
                Tables\Columns\TextColumn::make('Incidente')
                    ->label('Incidente'),
                Tables\Columns\TextColumn::make('TipoRespuesta')
                    ->label('Tipo Respuesta')
                    ->limit(35),
            ])
            ->defaultSort('CodigoUnidad')
            ->paginated([10, 25, 50])
            ->poll('30s');
    }
}
