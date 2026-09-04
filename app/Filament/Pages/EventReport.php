<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

/**
 * Page: EventReport
 * Nombre: Reporte de Eventos
 * Descripcion: Pagina con filtros de fecha/hora que muestra eventos del CAD en una tabla Livewire paginada.
 * Soporta busqueda por rango de fechas (buscarPorFecha) y por numero de evento (buscarPorEvento).
 * Tambien acepta el query param ?busqueda= para pre-cargar desde el widget de incidentes activos.
 */
class EventReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.event-report';

    protected static ?string $title = 'Reporte de Eventos';

    protected static ?string $navigationLabel = 'Reporte de Eventos';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-m-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Reportes';
    }

    public ?string $fechaDesde = null;

    public ?string $fechaHasta = null;

    public string $busqueda = '';

    public bool $busquedaEjecutada = false;

    /**
     * Si el visitante llega con ?busqueda=SE911:... en la URL (desde el widget de
     * Incidentes Activos sin Cerrar), pre-carga el campo y ejecuta la busqueda automaticamente.
     */
    public function mount(): void
    {
        $paramBusqueda = request()->query('busqueda', '');

        if (filled($paramBusqueda)) {
            $this->busqueda = $paramBusqueda;
            $this->buscarPorEvento();
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Filtros de Busqueda')
                    ->description('Selecciona un rango de fechas o ingresa el numero de evento')
                    ->icon('heroicon-m-funnel')
                    ->schema([
                        DateTimePicker::make('fechaDesde')
                            ->label('Fecha Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('America/El_Salvador'),

                        DateTimePicker::make('fechaHasta')
                            ->label('Fecha Hasta')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('America/El_Salvador'),

                        Forms\Components\TextInput::make('busqueda')
                            ->label('Buscar por Numero de Evento')
                            ->placeholder('Ej: 279215 o SE911:2026:09:04:0001')
                            ->prefixIcon('heroicon-m-magnifying-glass'),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * Busca eventos por rango de fechas.
     * Las fechas Desde y Hasta son obligatorias.
     */
    public function buscarPorFecha(): void
    {
        $this->validate([
            'fechaDesde' => 'required',
            'fechaHasta' => 'required',
        ]);

        $desde = Carbon::parse($this->fechaDesde, 'America/El_Salvador')->format('Ymd H:i:s');
        $hasta = Carbon::parse($this->fechaHasta, 'America/El_Salvador')->format('Ymd H:i:s');

        $this->busquedaEjecutada = true;

        // Envia busqueda vacia para que EventReportTable filtre solo por fechas
        $this->dispatch('search', $desde, $hasta, '');
    }

    /**
     * Busca un evento especifico por numero de evento (sin requerir fechas).
     */
    public function buscarPorEvento(): void
    {
        $this->validate([
            'busqueda' => 'required',
        ], [
            'busqueda.required' => 'Ingresa el numero de evento.',
        ]);

        $this->busquedaEjecutada = true;

        // Envia fechas vacias para que EventReportTable active la ruta de busqueda por evento
        $this->dispatch('search', '', '', $this->busqueda);
    }
}
