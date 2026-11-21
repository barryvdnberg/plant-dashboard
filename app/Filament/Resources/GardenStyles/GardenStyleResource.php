<?php

namespace App\Filament\Resources\GardenStyles;

use App\Filament\Forms\Builder\GardenStyleForm;
use App\Filament\Resources\GardenStyles\Pages\CreateGardenStyle;
use App\Filament\Resources\GardenStyles\Pages\EditGardenStyle;
use App\Filament\Resources\GardenStyles\Pages\ListGardenStyles;
use App\Filament\Resources\GardenStyles\Pages\ViewGardenStyle;
use App\Filament\Resources\GardenStyles\Schemas\GardenStyleInfolist;
use App\Filament\Resources\GardenStyles\Tables\GardenStylesTable;
use App\Models\GardenStyle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GardenStyleResource extends Resource
{
    protected static ?string $model = GardenStyle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Extras';

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
            GardenStyleForm::form()
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return GardenStyleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GardenStylesTable::configure($table);
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
            'index' => ListGardenStyles::route('/'),
            'create' => CreateGardenStyle::route('/create'),
            'edit' => EditGardenStyle::route('/{record}/edit'),
        ];
    }
}
