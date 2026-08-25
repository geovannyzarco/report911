<?php

namespace App\Filament\Widgets;

use App\Services\CadReportService;
use Filament\Widgets\ChartWidget;

/**
 * Widget: IncidentClassificationChart
 * Nombre: Incidentes por Tipo
 * Descripcion: Grafica de dona que muestra la cantidad de incidentes no cerrados
 * agrupados por tipo/clasificacion (robo, accidente, incendio, etc.).
 * Filtra incidentes con status distinto a Terminado(6) y Cerrado(7).
 * usa CadReportService::getIncidentesPorClasificacion() para obtener los datos.
 * Se refresca automaticamente cada 30 segundos via polling de Filament.
 */
class IncidentClassificationChart extends ChartWidget
{
    // Titulo del widget
    protected ?string $heading = 'Incidentes por Tipo';

    // Intervalo de polling: cada 30 segundos se re-ejecuta getData() via AJAX
    protected ?string $pollingInterval = '30s';

    // Altura de la grafica en pixeles
    protected ?string $maxHeight = '300';

    /**
     * Obtiene los datos para la grafica de dona.
     * Retorna un array con 'labels' (nombres de clasificacion) y 'data' (cantidades).
     *
     * @return array{labels: array<int, string>, datasets: array<int, array{data: array<int, int>, backgroundColor: array<int, string>}>}
     */
    protected function getData(): array
    {
        // Consulta los incidentes no cerrados agrupados por clasificacion
        $stats = (new CadReportService)->getIncidentesPorClasificacion();

        // Colores para cada segmento de la dona (paleta de 8 colores)
        $colores = [
            '#3B82F6',  // azul
            '#EF4444',  // rojo
            '#F59E0B',  // amarillo
            '#10B981',  // verde
            '#8B5CF6',  // morado
            '#EC4899',  // rosa
            '#06B6D4',  // cyan
            '#F97316',  // naranja
        ];

        return [
            // Etiquetas del eje X (nombres de las clasificaciones)
            'labels' => $stats['labels'],
            // Conjunto de datos de la grafica
            'datasets' => [
                [
                    // Valores numericos (cantidad de incidentes por tipo)
                    'data' => $stats['data'],
                    // Colores de fondo de cada segmento de la dona
                    'backgroundColor' => array_slice($colores, 0, count($stats['data'])),
                ],
            ],
        ];
    }

    /**
     * Retorna el tipo de grafica a renderizar.
     * 'doughnut' = grafica de dona (circulo con hueco central).
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}
