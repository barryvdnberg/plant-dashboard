<?php

namespace App\Filament\Resources\Localizables;

use App\Filament\Forms\Builder\LocalizableForm;
use App\Filament\Resources\Localizables\Pages\CreateLocalizable;
use App\Filament\Resources\Localizables\Pages\EditLocalizable;
use App\Filament\Resources\Localizables\Pages\ListLocalizables;
use App\Filament\Resources\Localizables\Tables\LocalizablesTable;
use App\Models\Localizable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LocalizableResource extends Resource
{
    protected static ?string $model = Localizable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static string|UnitEnum|null $navigationGroup = 'Extras';

    protected static ?string $recordTitleAttribute = 'field';

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
            LocalizableForm::form()
        );
    }

    public static function table(Table $table): Table
    {
        return LocalizablesTable::configure($table);
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
            'index' => ListLocalizables::route('/'),
            'create' => CreateLocalizable::route('/create'),
            'edit' => EditLocalizable::route('/{record}/edit'),
        ];
    }
}
