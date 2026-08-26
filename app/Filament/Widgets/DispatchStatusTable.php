<?php

namespace App\Filament\Widgets;

use App\Models\Cad\Response;
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

        $query = Response::query()
            ->select([
                'st.Name as Estado',
                DB::raw('COUNT(*) as Cantidad'),
            ])
            ->join('Incidents as i', 'Responses.Incident', '=', 'i.OID')
            ->join('Statuses as st', 'Responses.Status', '=', 'st.OID')
            ->where(function ($q) {
                $q->where('i.Deleted', 0)->orWhereNull('i.Deleted');
            })
            ->whereRaw("i.CreationTime >= '$hoy'")
            ->groupBy('st.Name')
            ->orderByDesc('Cantidad');

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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('Cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->weight('bold'),
            ])
            ->paginated(false);
    }
}
