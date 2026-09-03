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
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

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
     * Define las tres tarjetas de alertas de incidentes abiertos.
     * Cada Stat representa un tipo de incidente que necesita atencion.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        // Consulta las estadisticas de incidentes abiertos del dia desde SQL Server
        $stats = (new CadReportService)->getEstadisticasIncidentesAbiertos();

        return [
            // Tarjeta 1 (roja): Incidentes que no tienen ningun despacho/response asociado
            // Significa que nadie ha sido despachado a este incidente
            Stat::make('Sin Despacho', number_format($stats['sin_despacho']))
                ->description('Incidentes sin respuesta asignada')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // Tarjeta 2 (amarilla): Incidentes que estan activos y no han sido cerrados
            // Incluye Req_Despacho, Despachado, En Ruta, En Sitio, Apilada
            Stat::make('Sin Cerrar', number_format($stats['sin_cerrar']))
                ->description('Incidentes activos / no finalizados')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Tarjeta 3 (azul): Incidentes que tienen al menos un despacho pero
            // ninguno tiene unidades asignadas en la tabla Assign (Active=1)
            Stat::make('Sin Recursos', number_format($stats['sin_recursos']))
                ->description('Con despacho pero sin unidades asignadas')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
