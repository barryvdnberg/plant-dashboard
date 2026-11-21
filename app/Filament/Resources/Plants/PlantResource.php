<?php

namespace App\Filament\Resources\Plants;

use App\Filament\Forms\Builder\PlantForm;
use App\Filament\Resources\Concerns\HasLocalizedRecordTitle;
use App\Filament\Resources\Plants\Pages\CreatePlant;
use App\Filament\Resources\Plants\Pages\EditPlant;
use App\Filament\Resources\Plants\Pages\ListPlants;
use App\Filament\Resources\Plants\RelationManagers\CultivarsRelationManager;
// use App\Filament\Resources\Plants\Schemas\PlantForm;
use App\Filament\Resources\Plants\Schemas\PlantInfolist;
use App\Filament\Resources\Plants\Tables\PlantsTable;
use App\Models\Plant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlantResource extends Resource
{
    use HasLocalizedRecordTitle;

    protected static ?string $model = Plant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sun;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return static::$model::getModelLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return static::$model::getPluralModelLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            ...PlantForm::form(),
            PlantForm::getBaseCultivarForm(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PlantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CultivarsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlants::route('/'),
            'create' => CreatePlant::route('/create'),
            // 'view' => ViewPlant::route('/{record}'),
            'edit' => EditPlant::route('/{record}/edit'),
        ];
    }
}
