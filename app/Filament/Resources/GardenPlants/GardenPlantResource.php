<?php

namespace App\Filament\Resources\GardenPlants;

use App\Filament\Forms\Builder\GardenPlantForm;
use App\Filament\Resources\GardenPlants\Pages\CreateGardenPlant;
use App\Filament\Resources\GardenPlants\Pages\EditGardenPlant;
use App\Filament\Resources\GardenPlants\Pages\ListGardenPlants;
use App\Filament\Resources\GardenPlants\Tables\GardenPlantsTable;
use App\Models\GardenPlant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GardenPlantResource extends Resource
{
    protected static ?string $model = GardenPlant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
        return $schema->components(
            GardenPlantForm::form()
        );
    }

    public static function table(Table $table): Table
    {
        return GardenPlantsTable::configure($table);
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
            'index' => ListGardenPlants::route('/'),
            'create' => CreateGardenPlant::route('/create'),
            'edit' => EditGardenPlant::route('/{record}/edit'),
        ];
    }
}
