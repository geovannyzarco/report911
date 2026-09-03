<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Widget: IncidentTypesTable
 * Nombre: Tipos de Incidente sin Cerrar
 * Descripcion: Muestra una tabla con los tipos de incidente (ResponseType)
 * que no han sido cerrados hoy y la cantidad de cada uno.
 */
class IncidentTypesTable extends BaseWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    protected static ?string $heading = 'Top 5 de Incidentes sin Cerrar (Hoy)';

    protected static ?int $sort = 5;

    protected static string|false $pollingInterval = false;

    /**
     * Define la estructura de la tabla.
     */
    public function table(Table $table): Table
    {
        $hoy = Carbon::today()->format('Ymd');

        // Consulta: top 5 tipos de incidente no cerrados hoy
        $resultados = DB::connection('sqlsrv_cad')->select("
            SELECT TOP 5 rt.Name as TipoIncidente, COUNT(DISTINCT i.OID) as Cantidad
            FROM Incidents i WITH (NOLOCK)
            INNER JOIN Responses r WITH (NOLOCK) ON r.Incident = i.OID
            INNER JOIN ResponseTypes rt WITH (NOLOCK) ON r.ResponseType = rt.OID
            WHERE (i.Deleted = 0 OR i.Deleted IS NULL)
            AND i.CreationTime >= '$hoy'
            AND i.Status NOT IN (6, 7)
            GROUP BY rt.Name
            ORDER BY Cantidad DESC
        ");

        // Convierte a array indexado para Filament
        $records = [];
        foreach ($resultados as $index => $row) {
            $records[$index] = [
                'id' => $index,
                'tipo' => $row->TipoIncidente,
                'cantidad' => (int) $row->Cantidad,
            ];
        }

        return $table
            ->records(fn() => $records)
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo de Incidente')
                    ->limit(50)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
            ])
            ->defaultSort('cantidad', 'desc');
    }
}
