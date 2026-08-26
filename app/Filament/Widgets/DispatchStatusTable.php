<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Widget: DispatchStatusTable
 * Nombre: Estado de Despachos (Tabla)
 * Descripcion: Muestra una tabla con los estados de despacho y sus cantidades del dia.
 * Usa un widget Blade en lugar de Filament TableWidget para evitar problemas
 * con GROUP BY y el ORDER BY automatico de Filament.
 */
class DispatchStatusTable extends Widget
{
    protected static ?string $heading = 'Detalle de Despachos por Estado';

    protected static ?int $sort = 4;

    protected static string|false $pollingInterval = false;

    /**
     * Retorna la vista Blade para este widget.
     */
    protected function getView(): string
    {
        return 'filament.widgets.dispatch-status-table';
    }

    /**
     * Datos de la tabla: estados y cantidades.
     */
    public array $estados = [];

    public int $total = 0;

    /**
     * Carga los datos al inicializar el widget.
     */
    public function mount(): void
    {
        $this->cargarDatos();
    }

    /**
     * Recarga los datos (para uso futuro con polling).
     */
    public function cargarDatos(): void
    {
        $hoy = Carbon::today()->format('Ymd');

        $resultados = DB::connection('sqlsrv_cad')->select("
            SELECT st.Name as Estado, COUNT(*) as Cantidad
            FROM Responses r WITH (NOLOCK)
            INNER JOIN Incidents i WITH (NOLOCK) ON r.Incident = i.OID
            INNER JOIN Statuses st WITH (NOLOCK) ON r.Status = st.OID
            WHERE (i.Deleted = 0 OR i.Deleted IS NULL)
            AND i.CreationTime >= '$hoy'
            GROUP BY st.Name
            ORDER BY Cantidad DESC
        ");

        $this->estados = $resultados;
        $this->total = array_sum(array_column($resultados, 'Cantidad'));
    }
}
