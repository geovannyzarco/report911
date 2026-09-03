<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Widget: StatsOverview
 * Nombre: Resumen del Dia
 * Descripcion: Muestra tres tarjetas de estadisticas con los totales acumulados
 * del dia actual: incidentes creados, llamadas recibidas y despachos realizados.
 * Consulta la base de datos CAD (ViperCAD_Log) via CadReportService.
 * Se refresca automaticamente cada 30 segundos via polling de Filament.
 */
class StatsOverview extends StatsOverviewWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    /**
     * Define las tres tarjetas de estadisticas que se muestran en el widget.
     * Cada Stat representa un KPI del sistema CAD del dia actual.
     *
     * @return array<int, Stat>
     */

    // Orden de visualizacion en el dashboard (1=primer widget)
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Obtiene la fecha de hoy al inicio del dia (00:00:00)
        $hoy = Carbon::today();

        // Obtiene la fecha y hora actual (incluye hora, minutos, segundos)
        $finHoy = Carbon::now();

        // Crea una instancia del servicio de reportes CAD
        $service = new CadReportService;

        // Consulta el resumen estadistico: total de incidentes, llamadas y despachos
        // El metodo agrega una dia al rango superior para cubrir todo el dia actual
        $resumen = $service->getResumenEstadistico($hoy, $finHoy);

        return [

            // Tarjeta 1: Total de llamadas telefonicas recibidas hoy
            Stat::make('Llamadas Hoy', number_format($resumen['total_llamadas']))
                ->description('Total de llamadas recibidas hoy')
                ->descriptionIcon('heroicon-m-phone')                          // Icono de telefono
                ->color('success'),
            // Color verde
            // Tarjeta 2: Total de incidentes creados hoy
            Stat::make('Incidentes Hoy', number_format($resumen['total_incidentes']))
                ->description('Total de incidentes registrados hoy')          // Texto descriptivo debajo del numero
                ->descriptionIcon('heroicon-m-document-text')                 // Icono Heroicon junto a la descripcion
                ->color('primary'),                                            // Color azul de Filament

            // Tarjeta 3: Total de despachos (respuestas) realizados hoy
            /* Stat::make('Despachos Hoy', number_format($resumen['total_despachos']))
                ->description('Total de despachos realizados hoy')
                ->descriptionIcon('heroicon-m-truck')                          // Icono de camion/boton
                ->color('warning'),  */                                          // Color naranja/amarillo
        ];
    }
}
