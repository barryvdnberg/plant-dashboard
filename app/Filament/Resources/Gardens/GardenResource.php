<?php

namespace App\Filament\Resources\Gardens;

use App\Filament\Forms\Builder\GardenForm;
use App\Filament\Resources\Gardens\Pages\CreateGarden;
use App\Filament\Resources\Gardens\Pages\EditGarden;
use App\Filament\Resources\Gardens\Pages\ListGardens;
use App\Filament\Resources\Gardens\Pages\ViewGarden;
use App\Filament\Resources\Gardens\RelationManagers\PlantsRelationManager;
use App\Filament\Resources\Gardens\Schemas\GardenInfolist;
use App\Filament\Resources\Gardens\Tables\GardensTable;
use App\Models\Garden;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GardenResource extends Resource
{
    protected static ?string $model = Garden::class;

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
            GardenForm::form()
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return GardenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GardensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PlantsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGardens::route('/'),
            'create' => CreateGarden::route('/create'),
            // 'view' => ViewGarden::route('/{record}'),
            'edit' => EditGarden::route('/{record}/edit'),
        ];
    }

    
}
