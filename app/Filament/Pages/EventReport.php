<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Page: EventReport
 * Nombre: Reporte de Eventos
 * Descripcion: Pagina personalizada con tabla nativa de Filament para eventos del CAD.
 */
class EventReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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

    public array $resultados = [];

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

    public function table(Table $table): Table
    {
        $records = [];

        if ($this->busquedaEjecutada) {
            $records = array_values(array_filter($this->resultados, function ($row) {
                if (empty($this->busqueda)) {
                    return true;
                }

                return str_contains($row->{'Numero de Evento'}, $this->busqueda);
            }));

            $records = array_map(function ($index, $row) {
                return [
                    'id' => $index + 1,
                    'numero_evento' => $row->{'Numero de Evento'},
                    'tipo_evento' => $row->{'Tipo de Evento'},
                    'telefonista' => $row->Telefonista,
                    'despachador' => $row->Despachador,
                    'hora_llamada' => $row->{'Hora Llamada'} ?? '-',
                    'hora_creacion' => $row->{'Hora Creacion'} ?? '-',
                    'hora_despacho' => $row->{'Hora Despacho'} ?? '-',
                    'hora_en_sitio' => $row->{'Hora En Sitio'} ?? '-',
                    'hora_terminado' => $row->{'Hora Terminado'} ?? '-',
                    'hora_cierre' => $row->{'Hora Cierre'} ?? '-',
                    'tiempo_total' => $row->{'Tiempo Total'} ?? '-',
                ];
            }, array_keys($records), $records);
        }

        $perPage = 25;
        $paginator = new LengthAwarePaginator(
            $records,
            count($records),
            $perPage,
            request()->input('page', 1),
            ['path' => request()->url()]
        );

        return $table
            ->records(fn () => $paginator)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero_evento')
                    ->label('Evento')
                    ->weight('bold')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('tipo_evento')
                    ->label('Tipo')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record['tipo_evento']),

                Tables\Columns\TextColumn::make('telefonista')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record['telefonista']),

                Tables\Columns\TextColumn::make('despachador')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record['despachador']),

                Tables\Columns\TextColumn::make('hora_llamada')
                    ->label('Llamada')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hora_creacion')
                    ->label('Creacion')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hora_despacho')
                    ->label('Despacho')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hora_en_sitio')
                    ->label('En Sitio')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hora_terminado')
                    ->label('Terminado')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hora_cierre')
                    ->label('Cierre')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tiempo_total')
                    ->label('Tiempo')
                    ->weight('bold'),
            ])
            ->paginated(['10', '25', '50', '100'])
            ->defaultPaginationPageOption(25)
            ->searchable(false)
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public function buscar(): void
    {
        $this->validate([
            'fechaDesde' => 'required',
            'fechaHasta' => 'required',
        ]);

        $desde = Carbon::parse($this->fechaDesde, 'America/El_Salvador')->format('Ymd H:i:s');
        $hasta = Carbon::parse($this->fechaHasta, 'America/El_Salvador')->format('Ymd H:i:s');

        $query = "
            WITH cte_Calls AS (
                SELECT c.Incident, MIN(c.CreationTime) AS [HORA_LLAMADA_calls]
                FROM Calls c WITH (NOLOCK)
                WHERE c.Incident IN (
                    SELECT DISTINCT Incident FROM Responses WITH (NOLOCK)
                    WHERE CreationTime BETWEEN '$desde' AND '$hasta'
                )
                GROUP BY c.Incident
            ),
            cte_LlamadaEvento AS (
                SELECT a.Incident, a.ResponseType, a.SequenceNumber AS [NUMERO_SECUENCIA],
                    a.OID AS ResponseOID, g.Name AS [TIPO_RESPUESTA], d.[HORA_LLAMADA_calls],
                    a.CreationTime AS [FECHA_CREACION], i.Agent AS IncidentAgentOID,
                    CASE WHEN a.Status = 7 THEN a.StatusTime ELSE NULL END AS HoraCierre
                FROM Responses AS a WITH (NOLOCK)
                INNER JOIN cte_Calls AS d ON d.Incident = a.Incident
                INNER JOIN Incidents AS i WITH (NOLOCK) ON a.Incident = i.OID
                INNER JOIN ResponseTypes AS g WITH (NOLOCK) ON g.OID = a.ResponseType
                WHERE a.CreationTime BETWEEN '$desde' AND '$hasta'
            ),
            cte_tiempos AS (
                SELECT a.ResponseOID,
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.StatusTime END) AS [Despachado],
                    MAX(CASE WHEN c.Name = 'En Sitio' THEN am.StatusTime END) AS [En Sitio],
                    MAX(CASE WHEN c.Name = 'Terminado' THEN am.StatusTime END) AS [Terminado],
                    MAX(CASE WHEN c.Name = 'Despachado' THEN am.Agent END) AS DespachadorOID
                FROM cte_LlamadaEvento a
                INNER JOIN AssignModif am WITH (NOLOCK) ON am.Response = a.ResponseOID
                INNER JOIN Statuses c WITH (NOLOCK) ON c.OID = am.ResourceStatus
                GROUP BY a.ResponseOID
            )
            SELECT
                le.[NUMERO_SECUENCIA] AS [Numero de Evento],
                le.[TIPO_RESPUESTA] AS [Tipo de Evento],
                COALESCE(ag_tel.Firstname + ' ' + ag_tel.Lastname, 'Desconocido') AS [Telefonista],
                COALESCE(ag_dsp.Firstname + ' ' + ag_dsp.Lastname, 'Desconocido') AS [Despachador],
                CAST(le.[HORA_LLAMADA_calls] AS TIME(0)) AS [Hora Llamada],
                CAST(le.[FECHA_CREACION] AS TIME(0)) AS [Hora Creacion],
                CAST(tf.[Despachado] AS TIME(0)) AS [Hora Despacho],
                CAST(tf.[En Sitio] AS TIME(0)) AS [Hora En Sitio],
                CAST(tf.[Terminado] AS TIME(0)) AS [Hora Terminado],
                CAST(le.HoraCierre AS TIME(0)) AS [Hora Cierre],
                CONVERT(VARCHAR(8), DATEADD(SECOND,
                    CASE WHEN le.HoraCierre >= le.[FECHA_CREACION]
                    THEN DATEDIFF(SECOND, le.[FECHA_CREACION], le.HoraCierre) ELSE 0 END, 0), 108) AS [Tiempo Total]
            FROM cte_LlamadaEvento le
            LEFT JOIN cte_tiempos tf ON le.ResponseOID = tf.ResponseOID
            LEFT JOIN Agents ag_tel WITH (NOLOCK) ON le.IncidentAgentOID = ag_tel.OID
            LEFT JOIN Agents ag_dsp WITH (NOLOCK) ON tf.DespachadorOID = ag_dsp.OID
            ORDER BY le.[NUMERO_SECUENCIA]
        ";

        $this->resultados = DB::connection('sqlsrv_cad')->select($query);
        $this->busquedaEjecutada = true;
    }

    public function updatedBusqueda(): void
    {
        // Table refreshes automatically
    }
}
