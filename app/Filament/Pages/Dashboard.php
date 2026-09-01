<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActiveEventsWidget;
use App\Filament\Widgets\DispatchStatusTable;
use App\Filament\Widgets\DispatchStatusWidget;
use App\Filament\Widgets\IncidentAlertsWidget;
use App\Filament\Widgets\IncidentClassificationChart;
use App\Filament\Widgets\IncidentsByStatusChart;
use App\Filament\Widgets\IncidentTypesTable;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard personalizado del sistema de monitoreo CAD.
 * Controla que widgets se muestran y en que orden aparecen.
 */
class Dashboard extends BaseDashboard
{
    /**
     * Retorna los widgets que se muestran en el dashboard, en el orden especificado.
     *
     * @return array<int, string>
     */
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            IncidentAlertsWidget::class,
            DispatchStatusWidget::class,
            DispatchStatusTable::class,
            IncidentClassificationChart::class,
            IncidentTypesTable::class,
            IncidentsByStatusChart::class,
            ActiveEventsWidget::class,
        ];
    }
}
