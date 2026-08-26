<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget: DispatchStatusTable
 * Nombre: Estado de Despachos (Tabla)
 * Descripcion: Muestra una tabla con los estados de despacho y sus cantidades.
 * Misma data que DispatchStatusWidget pero en formato tabla.
 */
class DispatchStatusTable extends BaseWidget
{
    // Titulo del widget
    protected static ?string $heading = 'Detalle de Despachos por Estado';

    // Orden de visualizacion en el dashboard
    protected static ?int $sort = 4;

    // Sin polling
    protected static string|false $pollingInterval = false;

    /**
     * Define la estructura de la tabla.
     *
     * @param  Table  $table  Instancia de Filament Table
     * @return Table Table configurada
     */
    public function table(Table $table): Table
    {
        // Obtiene los datos del servicio
        $stats = (new CadReportService)->getIncidentesPorEstado();

        // Convierte los datos a un array de filas para la tabla
        $filas = [];
        foreach ($stats['labels'] as $index => $estado) {
            $filas[] = (object) [
                'Estado' => $estado,
                'Cantidad' => $stats['data'][$index] ?? 0,
            ];
        }

        // Calcula el total para el porcentaje
        $total = array_sum($stats['data']);

        return $table
            ->query(fn () => collect($filas))
            ->columns([
                // Columna 1: Nombre del estado con badge de color
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
                        'Apilada' => 'gray',
                        default => 'gray',
                    })
                    ->weight('bold')
                    ->sortable(),

                // Columna 2: Cantidad de despachos
                Tables\Columns\TextColumn::make('Cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),

                // Columna 3: Porcentaje del total
                Tables\Columns\TextColumn::make('Estado')
                    ->label('Porcentaje')
                    ->formatStateUsing(function ($state) use ($total): string {
                        // Busca la cantidad de este estado en las filas
                        $cantidad = 0;
                        foreach ($this->getFilas() as $fila) {
                            if ($fila->Estado === $state) {
                                $cantidad = $fila->Cantidad;
                                break;
                            }
                        }
                        $porcentaje = $total > 0 ? round(($cantidad / $total) * 100, 1) : 0;

                        return "{$porcentaje}%";
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Cerrado' => 'success',
                        'Terminado' => 'info',
                        'En Sitio' => 'danger',
                        'En Ruta' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('Cantidad', 'desc')
            ->paginated(false);
    }

    /**
     * Obtiene las filas para uso interno.
     */
    private function getFilas(): array
    {
        $stats = (new CadReportService)->getIncidentesPorEstado();
        $filas = [];
        foreach ($stats['labels'] as $index => $estado) {
            $filas[] = (object) [
                'Estado' => $estado,
                'Cantidad' => $stats['data'][$index] ?? 0,
            ];
        }

        return $filas;
    }
}
