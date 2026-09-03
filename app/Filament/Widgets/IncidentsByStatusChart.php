<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Widgets\ChartWidget;

/**
 * Widget: IncidentsByStatusChart
 * Nombre: Incidentes por Estado
 * Descripcion: Grafica de barras horizontal que muestra la cantidad de incidentes
 * del dia actual agrupados por estado (Req_Despacho, En Ruta, En Sitio, Terminado, etc.).
 * Usa CadReportService::getIncidentesPorEstado() para obtener los datos.
 */
class IncidentsByStatusChart extends ChartWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    // Titulo del widget
    protected ?string $heading = 'Estado de Despachos (Hoy)';

    // Altura de la grafica en pixeles
    protected ?string $maxHeight = '300';

    // Orden de visualizacion en el dashboard
    protected static ?int $sort = 3;

    /**
     * Obtiene los datos para la grafica de barras.
     *
     * @return array{labels: array<int, string>, datasets: array<int, array{data: array<int, int>, backgroundColor: array<int, string>}>}
     */
    protected function getData(): array
    {
        $stats = (new CadReportService)->getIncidentesPorEstado();

        // Colores por tipo de estado
        $colores = [
            '#EF4444',  // Req_Despacho - rojo (urgente)
            '#F59E0B',  // Despachado - amarillo
            '#3B82F6',  // En Ruta - azul
            '#8B5CF6',  // En Sitio - morado
            '#10B981',  // Terminado - verde
            '#6B7280',  // Cerrado - gris
            '#EC4899',  // Apilada - rosa
            '#06B6D4',  // Otro - cyan
        ];

        return [
            'labels' => $stats['labels'],
            'datasets' => [
                [
                    'data' => $stats['data'],
                    'backgroundColor' => array_slice($colores, 0, count($stats['data'])),
                ],
            ],
        ];
    }

    /**
     * Tipo de grafica: barras horizontales.
     */
    protected function getType(): string
    {
        return 'bar';
    }
}
