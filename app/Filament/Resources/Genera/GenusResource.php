<?php

namespace App\Filament\Resources\Genera;

use App\Filament\Forms\Builder\GenusForm;
use App\Filament\Resources\Concerns\HasLocalizedRecordTitle;
use App\Filament\Resources\Genera\Pages\CreateGenus;
use App\Filament\Resources\Genera\Pages\EditGenus;
use App\Filament\Resources\Genera\Pages\ListGenera;
use App\Filament\Resources\Genera\RelationManagers\SpeciesRelationManager;
use App\Filament\Resources\Genera\Schemas\GenusInfolist;
use App\Filament\Resources\Genera\Tables\GeneraTable;
use App\Models\Genus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GenusResource extends Resource
{
    use HasLocalizedRecordTitle;

    protected static ?string $model = Genus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

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
            GenusForm::form()
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return GenusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneraTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SpeciesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGenera::route('/'),
            'create' => CreateGenus::route('/create'),
            // 'view' => ViewGenus::route('/{record}'),
            'edit' => EditGenus::route('/{record}/edit'),
        ];
    }
}
