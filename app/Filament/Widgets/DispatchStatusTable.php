<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Widget: DispatchStatusTable
 * Nombre: Estado de Despachos (Tabla)
 * Descripcion: Muestra una tabla con los estados de despacho y sus cantidades del dia.
 */
class DispatchStatusTable extends Widget
{
    protected static ?string $heading = 'Detalle de Despachos por Estado';

    protected static ?int $sort = 4;

    protected static string|false $pollingInterval = false;

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
     * Retorna la vista Blade para este widget.
     */
    public function render()
    {
        return view('filament.widgets.dispatch-status-table', [
            'estados' => $this->estados,
            'total' => $this->total,
            'heading' => static::$heading,
        ]);
    }

    /**
     * Recarga los datos.
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
