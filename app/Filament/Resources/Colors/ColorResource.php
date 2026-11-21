<?php

namespace App\Filament\Resources\Colors;

use App\Filament\Forms\Builder\ColorForm;
use App\Filament\Resources\Colors\Pages\CreateColor;
use App\Filament\Resources\Colors\Pages\EditColor;
use App\Filament\Resources\Colors\Pages\ListColors;
use App\Filament\Resources\Colors\Tables\ColorsTable;
use App\Models\Color;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

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
            ColorForm::form()
        );
    }

    public static function table(Table $table): Table
    {
        return ColorsTable::configure($table);
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
            'index' => ListColors::route('/'),
            'create' => CreateColor::route('/create'),
            'edit' => EditColor::route('/{record}/edit'),
        ];
    }
}
