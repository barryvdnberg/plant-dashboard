<?php

namespace App\Filament\Resources\Species;

use App\Filament\Forms\Builder\SpeciesForm;
use App\Filament\Resources\Concerns\HasLocalizedRecordTitle;
use App\Filament\Resources\Species\Pages\CreateSpecies;
use App\Filament\Resources\Species\Pages\EditSpecies;
use App\Filament\Resources\Species\Pages\ListSpecies;
use App\Filament\Resources\Species\RelationManagers\PlantsRelationManager;
use App\Filament\Resources\Species\Schemas\SpeciesInfolist;
use App\Filament\Resources\Species\Tables\SpeciesTable;
use App\Models\Species;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpeciesResource extends Resource
{
    use HasLocalizedRecordTitle;

    protected static ?string $model = Species::class;

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
            SpeciesForm::form()
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return SpeciesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpeciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PlantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpecies::route('/'),
            'create' => CreateSpecies::route('/create'),
            // 'view' => ViewSpecies::route('/{record}'),
            'edit' => EditSpecies::route('/{record}/edit'),
        ];
    }
}
