<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehiculoResource\Pages;
use App\Models\Vehiculo;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Resource de Filament para gestionar el catalogo maestro de vehiculos.
 */
class VehiculoResource extends Resource
{
    protected static ?string $model = Vehiculo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static string|UnitEnum|null $navigationGroup = 'Recursos';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'numero_equipo';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información del Vehículo')
                    ->schema([
                        Forms\Components\TextInput::make('numero_equipo')
                            ->label('Número de Equipo')
                            ->required()
                            ->unique(ignoreRecord: true)
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
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_equipo')
                    ->label('Número de Equipo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipoVehiculo.nombre')
                    ->label('Tipo de Vehículo')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sector.nombre')
                    ->label('Sector')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehiculos::route('/'),
            'create' => Pages\CreateVehiculo::route('/create'),
            'edit' => Pages\EditVehiculo::route('/{record}/edit'),
        ];
    }
}
