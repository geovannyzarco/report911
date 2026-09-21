<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TurnoResource\Pages;
use App\Models\Turno;
use App\Services\DespachoService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Resource de Filament para registrar los turnos de cada despacho
 * con los recursos (agentes y vehiculos) disponibles en el periodo.
 */
class TurnoResource extends Resource
{
    protected static ?string $model = Turno::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Turnos';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'inicio';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Datos del Turno')
                    ->schema([
                        Forms\Components\Select::make('despacho_id')
                            ->label('Despachador')
                            ->relationship('despacho', 'nombre')
                            ->preload()
                            ->searchable()
                            ->default(fn () => auth()->user()?->despacho?->id)
                            ->disabled(fn () => auth()->user()?->hasRole(DespachoService::ROL_DESPACHO))
                            ->required(),
                        Forms\Components\DateTimePicker::make('inicio')
                            ->label('Inicio')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\DateTimePicker::make('fin')
                            ->label('Fin')
                            ->seconds(false)
                            ->afterOrEqual('inicio'),
                    ])->columns(3),
                Section::make('Recursos del Turno')
                    ->schema([
                        Forms\Components\Repeater::make('turnoAgentes')
                            ->relationship()
                            ->label('Agentes disponibles')
                            ->schema([
                                Forms\Components\Select::make('agente_id')
                                    ->label('Agente')
                                    ->relationship('agente', 'nombre')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('oni')
                                            ->label('ONI')
                                            ->required()
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('nombre')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('categoria_id')
                                            ->label('Categoría')
                                            ->relationship('categoria', 'nombre')
                                            ->preload()
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\Select::make('sector_id')
                                            ->label('Sector')
                                            ->relationship('sector', 'nombre')
                                            ->preload()
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\TextInput::make('telefono_oni')
                                            ->label('Teléfono ONI')
                                            ->maxLength(20),
                                        Forms\Components\Toggle::make('activo')
                                            ->label('Activo')
                                            ->default(true),
                                    ]),
                                Forms\Components\Select::make('estado_recurso_id')
                                    ->label('Estado')
                                    ->relationship('estadoRecurso', 'nombre')
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Textarea::make('nota')
                                    ->label('Nota')
                                    ->rows(2)
                                    ->nullable(),
                            ])
                            ->collapsible()
                            ->grid(3)
                            ->addActionLabel('Agregar agente'),
                        Forms\Components\Repeater::make('turnoVehiculos')
                            ->relationship()
                            ->label('Vehículos disponibles')
                            ->schema([
                                Forms\Components\Select::make('vehiculo_id')
                                    ->label('Vehículo')
                                    ->relationship('vehiculo', 'numero_equipo')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('numero_equipo')
                                            ->label('Número de Equipo')
                                            ->required()
                                            ->maxLength(50),
                                        Forms\Components\Select::make('tipo_vehiculo_id')
                                            ->label('Tipo de Vehículo')
                                            ->relationship('tipoVehiculo', 'nombre')
                                            ->preload()
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\Select::make('sector_id')
                                            ->label('Sector')
                                            ->relationship('sector', 'nombre')
                                            ->preload()
                                            ->searchable()
                                            ->nullable(),
                                        Forms\Components\Toggle::make('activo')
                                            ->label('Activo')
                                            ->default(true),
                                    ]),
                                Forms\Components\Select::make('estado_recurso_id')
                                    ->label('Estado')
                                    ->relationship('estadoRecurso', 'nombre')
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Textarea::make('nota')
                                    ->label('Nota')
                                    ->rows(2)
                                    ->nullable(),
                            ])
                            ->collapsible()
                            ->grid(3)
                            ->addActionLabel('Agregar vehículo'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('despacho.nombre')
                    ->label('Despachador')
                    ->sortable(),
                Tables\Columns\TextColumn::make('inicio')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('turnoAgentes_count')
                    ->label('Agentes')
                    ->counts('turnoAgentes')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('turnoVehiculos_count')
                    ->label('Vehículos')
                    ->counts('turnoVehiculos')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Los despachadores solo ven sus propios turnos; los demas roles ven todos.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->hasRole(DespachoService::ROL_DESPACHO)) {
            $query->where('despacho_id', $user->despacho?->id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTurnos::route('/'),
            'create' => Pages\CreateTurno::route('/create'),
            'edit' => Pages\EditTurno::route('/{record}/edit'),
        ];
    }
}
