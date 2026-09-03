<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget: DispatchStatusWidget
 * Nombre: Estado de Despachos
 * Descripcion: Muestra tarjetas de estadisticas con la cantidad de despachos
 * agrupados por estado (Cerrado, Terminado, En Sitio, En Ruta, Req_Despacho).
 * Consulta SQL Server via CadReportService::getIncidentesPorEstado().
 */
class DispatchStatusWidget extends StatsOverviewWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    // Titulo del widget
    protected function getHeading(): string
    {
        return 'Estado de Despachos (Hoy)';
    }

    // Orden de visualizacion en el dashboard
    protected static ?int $sort = 3;

    // Sin polling: consulta sobre tablas grandes
    protected function getPollingInterval(): ?string
    {
        return null;
    }

    /**
     * Define las tarjetas de estadisticas por estado de despacho.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $stats = (new CadReportService)->getIncidentesPorEstado();

        // Colores por tipo de estado
        $colores = [
            'Cerrado' => 'success',
            'Terminado' => 'info',
            'En Sitio' => 'danger',
            'En Ruta' => 'warning',
            'Req_Despacho' => 'gray',
            'Despachado' => 'info',
            'Apilada' => 'gray',
        ];

        // Iconos por tipo de estado
        $iconos = [
            'Cerrado' => 'heroicon-m-check-circle',
            'Terminado' => 'heroicon-m-check-badge',
            'En Sitio' => 'heroicon-m-map-pin',
            'En Ruta' => 'heroicon-m-truck',
            'Req_Despacho' => 'heroicon-m-exclamation-triangle',
            'Despachado' => 'heroicon-m-paper-airplane',
            'Apilada' => 'heroicon-m-square-3-stack-3d',
        ];

        $cards = [];

        foreach ($stats['labels'] as $index => $estado) {
            $total = $stats['data'][$index] ?? 0;
            $color = $colores[$estado] ?? 'gray';
            $icono = $iconos[$estado] ?? 'heroicon-m-information-circle';

            $cards[] = Stat::make($estado, number_format($total))
                ->description("Despachos {$estado}")
                ->descriptionIcon($icono)
                ->color($color);
        }

        return $cards;
    }
}
