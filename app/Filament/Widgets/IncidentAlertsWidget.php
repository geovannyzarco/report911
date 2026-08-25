<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IncidentAlertsWidget extends StatsOverviewWidget
{
    protected function getHeading(): string
    {
        return 'Incidentes Abiertos';
    }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        $stats = (new CadReportService)->getEstadisticasIncidentesAbiertos();

        return [
            Stat::make('Sin Despacho', number_format($stats['sin_despacho']))
                ->description('Incidentes sin respuesta asignada')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make('Sin Cerrar', number_format($stats['sin_cerrar']))
                ->description('Incidentes activos / no finalizados')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Sin Recursos', number_format($stats['sin_recursos']))
                ->description('Con despacho pero sin unidades asignadas')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
