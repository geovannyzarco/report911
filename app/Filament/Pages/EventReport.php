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

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Filtros de Busqueda')
                    ->description('Selecciona un rango de fechas para consultar los eventos')
                    ->icon('heroicon-m-funnel')
                    ->schema([
                        DateTimePicker::make('fechaDesde')
                            ->label('Fecha Desde')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('America/El_Salvador'),

                        DateTimePicker::make('fechaHasta')
                            ->label('Fecha Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('America/El_Salvador'),

                        Forms\Components\TextInput::make('busqueda')
                            ->label('Buscar por Numero de Evento')
                            ->placeholder('Ej: 279215')
                            ->prefixIcon('heroicon-m-magnifying-glass'),
                    ])
                    ->columns(3),
            ]);
    }

    public function buscar(): void
    {
        $this->validate([
            'fechaDesde' => 'required',
            'fechaHasta' => 'required',
        ]);

        $desde = Carbon::parse($this->fechaDesde, 'America/El_Salvador')->format('Ymd H:i:s');
        $hasta = Carbon::parse($this->fechaHasta, 'America/El_Salvador')->format('Ymd H:i:s');

        $this->busquedaEjecutada = true;

        $this->dispatch('search', $desde, $hasta, $this->busqueda ?? '');
    }
}
