<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Widget: DispatchStatusTable
 * Nombre: Estado de Despachos (Tabla)
 * Descripcion: Muestra una tabla con los estados de despacho y sus cantidades del dia.
 */
class DispatchStatusTable extends BaseWidget
{
    protected static ?string $heading = 'Detalle de Despachos por Estado';

    protected static ?int $sort = 4;

    protected static string|false $pollingInterval = false;

    public function table(Table $table): Table
    {
        $hoy = Carbon::today()->format('Ymd');

        $query = DB::connection('sqlsrv_cad')->table('Responses as r')
            ->select([
                'st.Name as Estado',
                DB::raw('COUNT(*) as Cantidad'),
            ])
            ->join('Incidents as i', 'r.Incident', '=', 'i.OID')
            ->join('Statuses as st', 'r.Status', '=', 'st.OID')
            ->where(function ($q) {
                $q->where('i.Deleted', 0)->orWhereNull('i.Deleted');
            })
            ->whereRaw("i.CreationTime >= '$hoy'")
            ->groupBy('st.Name');

        return $table
            ->query(fn () => $query)
            ->columns([
                Tables\Columns\TextColumn::make('Estado')
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
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('Cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
            ])
            ->defaultSort('Cantidad', 'desc')
            ->paginated(false);
    }
}
