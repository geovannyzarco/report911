<?php

namespace App\Filament\Widgets;

use App\Models\Cad\Incident;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActiveEventsWidget extends BaseWidget
{
    protected static ?string $heading = 'Incidentes Activos (Ultimas 24h)';

    protected static ?int $sort = 2;

    protected static string|false $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        $desde = Carbon::now()->subDay()->format('Ymd');

        $query = Incident::query()
            ->select([
                'Incidents.OID',
                'Incidents.SequenceNumber',
                'Incidents.CreationTime',
                'cl.Name as Clasificacion',
                'pr.Name as Prioridad',
                'st.Name as Estado',
                'ag.Name as Agencia',
                DB::raw('COALESCE(a.DisplayName, a.LogonName) as Operador'),
            ])
            ->leftJoin('Classifications as cl', 'Incidents.Classification', '=', 'cl.OID')
            ->leftJoin('Priorities as pr', 'Incidents.Priority', '=', 'pr.OID')
            ->leftJoin('Statuses as st', 'Incidents.Status', '=', 'st.OID')
            ->leftJoin('Agencies as ag', 'Incidents.PrimaryAgency', '=', 'ag.OID')
            ->leftJoin('Agents as a', 'Incidents.Agent', '=', 'a.OID')
            ->whereNotIn('Incidents.Status', [6, 7, 8])
            ->where(function ($q) {
                $q->where('Incidents.Deleted', 0)->orWhereNull('Incidents.Deleted');
            })
            ->whereRaw("Incidents.CreationTime >= '$desde'");

        return $table
            ->query(fn () => $query)
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
