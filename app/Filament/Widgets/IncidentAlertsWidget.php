<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget: IncidentAlertsWidget
 * Nombre: Incidentes Abiertos
 * Descripcion: Muestra tres tarjetas de alertas con la cantidad de incidentes
 * del dia actual que requieren atencion urgente:
 *   - Sin Despacho: incidentes que no tienen ningun Response (despacho) asociado
 *   - Sin Cerrar: incidentes cuyo status no es Terminado(6) ni Cerrado(7)
 *   - Sin Recursos: incidentes que tienen despacho pero ninguna unidad asignada
 * Consulta SQL Server via CadReportService::getEstadisticasIncidentesAbiertos().
 * Se refresca automaticamente cada 30 segundos via polling de Filament.
 */
class IncidentAlertsWidget extends StatsOverviewWidget
{
    /**
     * Retorna el titulo que se muestra encima del widget en el dashboard.
     */
    // Orden de visualizacion en el dashboard (1=primer widget)
    protected static ?int $sort = 2;

    protected function getHeading(): string
    {
        return 'Incidentes Abiertos';
    }

    /**
     * Retorna el intervalo de polling para refrescar los datos automaticamente.
     * '30s' = cada 30 segundos Filament re-ejecuta getStats() via AJAX.
     */
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    /**
     * Define las tarjetas de alertas de incidentes abiertos.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $stats = (new CadReportService)->getEstadisticasIncidentesAbiertos();

        return [
            // Tarjeta 1 (roja): Incidentes sin ninguna unidad asignada en Assign
            Stat::make('Sin Despacho', number_format($stats['sin_despacho']))
                ->description('Sin unidad asignada')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // Tarjeta 2 (amarilla): Incidentes activos no finalizados
            Stat::make('Sin Cerrar', number_format($stats['sin_cerrar']))
                ->description('Activos / no finalizados')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Tarjeta 3 (azul): Total de incidentes hoy
            Stat::make('Total Hoy', number_format($stats['total']))
                ->description('Incidentes creados hoy')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
        ];
    }
}
