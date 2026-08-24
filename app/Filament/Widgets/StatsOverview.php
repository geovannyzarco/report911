<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Widget de estadísticas generales del sistema CAD.
 * Muestra totales de incidentes, llamadas y despachos del día actual.
 */
class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $hoy = Carbon::today();
        $finHoy = Carbon::now();

        $service = new CadReportService;
        $resumen = $service->getResumenEstadistico($hoy, $finHoy);

        return [
            Stat::make('Incidentes Hoy', number_format($resumen['total_incidentes']))
                ->description('Total de incidentes registrados hoy')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Llamadas Hoy', number_format($resumen['total_llamadas']))
                ->description('Total de llamadas recibidas hoy')
                ->descriptionIcon('heroicon-m-phone')
                ->color('success'),
            Stat::make('Despachos Hoy', number_format($resumen['total_despachos']))
                ->description('Total de despachos realizados hoy')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
        ];
    }
}
