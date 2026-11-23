<?php

namespace App\Filament\Resources\Cultivars;

use App\Enums\NameDisplay;
use App\Filament\Forms\Builder\CultivarForm;
use App\Filament\Resources\Cultivars\Pages\CreateCultivar;
use App\Filament\Resources\Cultivars\Pages\EditCultivar;
use App\Filament\Resources\Cultivars\Pages\ListCultivars;
use App\Filament\Resources\Cultivars\Schemas\CultivarInfolist;
use App\Filament\Resources\Cultivars\Tables\CultivarsTable;
use App\Models\Cultivar;
use App\Support\NameDisplayPreferences;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CultivarResource extends Resource
{
    protected static ?string $model = Cultivar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if ($record instanceof Cultivar && app(NameDisplayPreferences::class)->forModel($record) !== NameDisplay::Scientific) {
            $record->loadMissing('localizations');

            if ($record->getTranslation() === null) {
                $record->loadMissing('parent.localizations');
            }
        }

        return $record?->display_name;
    }

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
            CultivarForm::form()
        );
    }

    public static function infolist(Schema $schema): Schema
    {
        return CultivarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CultivarsTable::configure($table)->recordTitle(fn (Cultivar $record): string => $record->display_name);
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
            'index' => ListCultivars::route('/'),
            'create' => CreateCultivar::route('/create'),
            // 'view' => ViewCultivar::route('/{record}'),
            'edit' => EditCultivar::route('/{record}/edit'),
        ];
    }
}
