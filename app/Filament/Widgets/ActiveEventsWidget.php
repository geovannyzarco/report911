<?php

namespace App\Filament\Widgets;

use App\Services\CadMonitorService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget que muestra los incidentes activos en tiempo real.
 */
class ActiveEventsWidget extends BaseWidget
{
    protected static ?string $heading = 'Incidentes Activos (Ultimas 24h)';

    protected static ?int $sort = 2;

    protected static string|false $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => (new CadMonitorService)->getEventosActivos())
            ->columns([
                Tables\Columns\TextColumn::make('SequenceNumber')
                    ->label('Incidente')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('CreationTime')
                    ->label('Hora')
                    ->dateTime('H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'En Ruta') => 'warning',
                        str_contains($state, 'En Sitio') => 'danger',
                        str_contains($state, 'Despachado') => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('Clasificacion')
                    ->label('Clasificacion')
                    ->limit(30),
                Tables\Columns\TextColumn::make('Prioridad')
                    ->label('Prioridad'),
                Tables\Columns\TextColumn::make('Agencia')
                    ->label('Agencia')
                    ->limit(25),
                Tables\Columns\TextColumn::make('Operador')
                    ->label('Operador'),
            ])
            ->defaultSort('CreationTime', 'desc')
            ->paginated([10, 25, 50])
            ->poll('30s');
    }
}
