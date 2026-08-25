<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActiveEventsWidget;
use App\Filament\Widgets\IncidentAlertsWidget;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard personalizado del sistema de monitoreo CAD.
 * Controla que widgets se muestran y en que orden aparecen.
 */
class Dashboard extends BaseDashboard
{
    /**
     * Retorna los widgets que se muestran en el dashboard.
     * El orden del array define el orden de visualizacion.
     */
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,            // 1. Resumen del dia (incidentes, llamadas, despachos)
            IncidentAlertsWidget::class,     // 2. Incidentes abiertos (sin despacho, sin cerrar, sin recursos)
            ActiveEventsWidget::class,       // 3. Tabla de incidentes activos ultimas 24h
        ];
    }
}
