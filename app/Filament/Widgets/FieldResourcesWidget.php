<?php

namespace App\Filament\Widgets;

use App\Models\Cad\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FieldResourcesWidget extends BaseWidget
{
    protected static ?string $heading = 'Unidades en Campo';

    protected static ?int $sort = 3;

    protected static string|false $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        $query = Resource::query()
            ->select([
                'Resources.OID',
                'Resources.Name as CodigoUnidad',
                'st.Name as EstadoUnidad',
                'sta.Name as Estacion',
                'i.SequenceNumber as Incidente',
                'rt.Name as TipoRespuesta',
            ])
            ->leftJoin('Statuses as st', 'Resources.Status', '=', 'st.OID')
            ->leftJoin('Stations as sta', 'Resources.Station', '=', 'sta.OID')
            ->leftJoin('Responses as r', 'Resources.ActiveResponse', '=', 'r.OID')
            ->leftJoin('Incidents as i', 'Resources.ActiveIncident', '=', 'i.OID')
            ->leftJoin('ResponseTypes as rt', 'r.ResponseType', '=', 'rt.OID')
            ->where('Resources.ActiveResponse', '!=', 0)
            ->whereNotNull('Resources.ActiveResponse');

        return $table
            ->query(fn () => $query)
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
