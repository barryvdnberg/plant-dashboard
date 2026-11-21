<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\Gardens\Pages\CreateGarden;
use App\Filament\App\Resources\Gardens\Pages\EditGarden;
use App\Filament\App\Resources\Gardens\Pages\GardenActionDetailPage;
use App\Filament\App\Resources\Gardens\Pages\GardenActionsPage;
use App\Filament\App\Resources\Gardens\Pages\GardenPlantsPage;
use App\Filament\App\Resources\Gardens\Pages\ListGardens;
use App\Filament\App\Resources\Gardens\Pages\ViewGarden;
use App\Filament\Forms\Builder\GardenForm;
use App\Models\Garden;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GardenResource extends Resource
{
    protected static ?string $model = Garden::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Scope all queries to the authenticated user's gardens.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Garden details'))
                    ->schema([
                        ...GardenForm::form(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plants_count')
                    ->label(__('Plants'))
                    ->counts('plants')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->recordUrl(fn (Garden $record) => GardenResource::getUrl('view', ['record' => $record]));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'         => ListGardens::route('/'),
            'create'        => CreateGarden::route('/create'),
            'view'          => ViewGarden::route('/{record}'),
            'edit'          => EditGarden::route('/{record}/edit'),
            'plants'        => GardenPlantsPage::route('/{record}/plants'),
            'actions'       => GardenActionsPage::route('/{record}/actions'),
            'action-detail' => GardenActionDetailPage::route('/{record}/actions/{gardenPlant}'),
        ];
    }
}
